<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Instas;


class InstaService
{
    private $repository;

    private $config;

    public function __construct(InstaRepository $repository, $config)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $tag
     * @param int    $limit
     * @return void
     * @throws \Exception
     */
    public function getInstasByHashtag(string $tag, int $limit, string $outputDir) : void
    {
       $instagram = new InstagramTag($tag);
       $extractedData = $this->extractData($instagram->getPosts($limit));
       $this->savePosts($tag, $extractedData, $outputDir);
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
                'location' => [
                    'id' => $post['location']['id'] ?? null,
                    'name' => $post['location']['name'] ?? '',
                    'slug' => $post['location']['slug'] ?? '',
                ],
                'videoStandardResolutionUrl' => $post['video_url'] ?? '',
                'videoViews' => (int) ($post['video_view_count'] ?? 0),
                'owner' => [
                    'username' => $post['owner']['username'],
                    'fullName' => $post['owner']['full_name'],
                    'profilePicUrl' => $post['owner']['profile_pic_url'],
                    'isPrivate' => (int) $post['owner']['is_private']
                ]
            ];
        }
        return $extractedData;
    }

    private function savePosts(string $tag, array $posts, string $outputDir) : void
    {
        foreach ($posts as $post) {
            echo ('Saving .' . $post['shortCode']. PHP_EOL);
            $this->repository->savePost($tag, $post, $outputDir);
        }
    }

    public function getPosts(string $tag, string $minId): array
    {
        return $this->repository->getPosts($tag, $minId);
    }
}
