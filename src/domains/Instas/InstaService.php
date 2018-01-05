<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Instas;

//use InstagramScraper\Instagram;
use InstagramAPI\Instagram;
use InstagramAPI\Signatures;

class InstaService
{
    private $instagramHandler;

    private $repository;

    private $config;

    public function __construct(InstaRepository $repository, $config)
    {
        $this->instagramHandler = new Instagram(); /* Instagram::withCredentials(
            $config['instagram']['username'],
            $config['instagram']['password'],
            '/tmp'
        );*/
        $this->repository = $repository;
        $this->config = $config['instagram'];
    }

    /**
     * @param string $username
     * @param int    $limit
     * @return array
     * @throws \Exception
     */
    public function getInstasByUsername(string $username, int $limit = 200) : array
    {
        $this->instagramHandler->login();
        try {
            $posts = $this->instagramHandler->getMedias($username, $limit);
            $accountInfo =  $this->instagramHandler->getAccountById($posts[0]->getOwnerId());
            return $this->populateInstasWithUserInfo($posts, $accountInfo);
        } catch (\Exception $e) {
            return ['status' => 404, 'reason' => $e->getMessage()];
        }
    }
    /**
     * @param string $hashtag
     * @param int    $limit
     * @return array
     * @throws \Exception
     */
    public function getInstasByHashtag(string $hashtag, int $limit) : array
    {
        $posts = [];
        try {
            $this->instagramHandler->login($this->config['username'], $this->config['password']);
            $rankToken = Signatures::generateUUID();
            $maxId = null;
            $count = 0;
            do {
                echo "in do\n";
                echo ((string) $maxId)." <- maxId\n";
                $response = $this->instagramHandler->hashtag->getFeed($hashtag, $rankToken, $maxId);
                $items = $response->getItems();
                foreach ($items as $item) {
                    $count++;
                    $post = $this->extractData(json_decode(json_encode($item), JSON_OBJECT_AS_ARRAY));;
                    $posts[] = $post;
                }
                $maxId = $response->getNextMaxId();
                echo "Sleeping for 5s...\n";
                sleep(5);
            } while ($count < $limit);

            var_dump($posts);

            if (count($posts)>0) {
                $this->savePosts($hashtag, $posts);
            }
            return $posts;
        } catch (\Exception $e) {
            return ['status' => 404, 'reason' => $e->getMessage()];
        } catch (\Error $e) {
            return ['status' => 404, 'reason' => $e->getMessage()];
        }
    }

    private function extractData(array $post) : array
    {
        return [
            'postId' => $post['pk'],
            'shortCode' => $post['code'],
            'createdTime' => $post['taken_at'],
            'type' => $post['media_type']===1? 'image': 'other',
            'link' => 'https://www.instagram.com/p/'.$post['code'],
            'imageThumbnailUrl' => $post['image_versions2'][1]['url'],
            'imageHighResolutionUrl' => $post['image_versions2'][0]['url'],
            'caption' => $post['caption']['text'],
            'ownerId' => $post['user']['pk'],
            'likesCount' => $post['like_count'],
            'locationId' => $post['location']['facebook_places_id'] ?? '',
            'locationName' => $post['location']['name'] ?? '',
            'owner' => [
                'username' => $post['user']['username'],
                'fullName' => $post['user']['full_name'],
                'profilePicUrl' => $post['user']['profile_pic_url'],
                'isPrivate' => (int) $post['user']['is_private']
            ]
        ];
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
