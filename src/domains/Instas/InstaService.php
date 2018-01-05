<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Instas;


class InstaService
{
    private $instagramHandler;

    private $repository;

    private $config;

    public function __construct(InstaRepository $repository, $config)
    {
        $this->repository = $repository;
        $this->config = $config['instagram'];
    }

    /**
     * @param string $hashtag
     * @param int    $limit
     * @return void
     * @throws \Exception
     */
    public function getInstasByHashtag(string $hashtag, int $limit) : void
    {
       $instagram = new InstagramTag($hashtag);
       $extractedData = $this->extractData($instagram->getPosts($limit));
       $this->savePosts($hashtag, $extractedData);
    }

    private function extractData(array $posts) : array
    {
        $extractedData = [];
        foreach ($posts as $post) {
            $extractedData[] = [
                'postId' => $post['id'],
                'shortCode' => $post['shortcode'],
                'createdTime' => $post['taken_at_timestamp'],
                'type' => $post['is_video'] ? 'video' : 'image',
                'link' => 'https://www.instagram.com/p/'.$post['shortcode'],
                'imageThumbnailUrl' => $post['display_resources'][0]['src'],
                'imageHighResolutionUrl' => $post['display_url'],
                'caption' => $post['edge_media_to_caption']['edges'][0]['node']['text'],
                'ownerId' => $post['owner']['id'],
                'likesCount' => $post['edge_media_preview_like']['count'],
                'locationId' => '',
                'locationName' => '',
                'owner' => [
                    'username' => $post['owner']['username'],
                    'fullName' => $post['owner']['full_name'],
                    'profilePicUrl' => $post['owner']['profile_pic_url'],
                    'isPrivate' => (int) $post['user']['is_private']
                ]
            ];
        }
        return $extractedData;
    }

    private function savePosts(string $hashtag, array $posts) : void
    {
        foreach ($posts as $post) {
            $this->repository->savePost($hashtag, $post);
        }
    }

    public function getPosts(string $hastag, string $minId): array
    {
        return $this->repository->getPosts($hastag, $minId);
    }
}
