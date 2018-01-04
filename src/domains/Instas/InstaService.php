<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Instas;

use InstagramScraper\Instagram;

class InstaService
{
    private $instagramHandler;

    private $repository;


    public function __construct(InstaRepository $repository, $config)
    {
        $this->instagramHandler = Instagram::withCredentials(
            $config['instagram']['username'],
            $config['instagram']['password'],
            '/tmp'
        );
        $this->repository = $repository;
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
        $this->instagramHandler->login();
        try {
            $posts = $this->instagramHandler->getMediasByTag($hashtag, $limit);
            $populatedPosts =  $this->populateInstasWithUserInfo($posts);
            $this->savePosts($hashtag, $populatedPosts);
            return $populatedPosts;
        } catch (\Exception $e) {
            return ['status' => 404, 'reason' => $e->getMessage()];
        }
    }

    /**
     * @param array                                $posts
     * @param \InstagramScraper\Model\Account|null $accountInfoProvided
     * @return array
     * @throws \InstagramScraper\Exception\InstagramException
     * @throws \InvalidArgumentException
     */
    private function populateInstasWithUserInfo(array $posts, $accountInfoProvided = null) : array
    {
        $populatedPosts = [];
        /**
         * @param \InstagramScraper\Model\Media $post
         */
        foreach ($posts as $post) {
            $ownerId = $post->getOwnerId();
            $accountInfo = $accountInfoProvided ?? $this->instagramHandler->getAccountById($ownerId);
            $populatedPosts[] = [
                'postId' => $post->getId(),
                'shortCode' => $post->getShortCode(),
                'createdTime' => $post->getCreatedTime(),
                'type' => $post->getType(),
                'link' => $post->getLink(),
                'imageThumbnailUrl' => $post->getImageThumbnailUrl(),
                'imageHighResolutionUrl' => $post->getImageHighResolutionUrl(),
                'caption' => $post->getCaption(),
                'videoStandardResolutionUrl' => $post->getVideoStandardResolutionUrl(),
                'videoViews' => $post->getVideoViews(),
                'ownerId' => $post->getOwnerId(),
                'likesCount' => $post->getLikesCount(),
                'locationId' => $post->getLocationId(),
                'locationName' => $post->getLocationName(),
                'commentsCount' => $post->getCommentsCount(),
                'owner' => [
                    'username' => $accountInfo->getUsername(),
                    'fullName' => $accountInfo->getFullName(),
                    'profilePicUrl' => $accountInfo->getProfilePicUrl(),
                    'biography' => $accountInfo->getBiography(),
                    'externalUrl' => $accountInfo->getExternalUrl(),
                    'followsCount' => $accountInfo->getFollowsCount(),
                    'followedByCount' => $accountInfo->getFollowedByCount(),
                    'mediaCount' => $accountInfo->getMediaCount(),
                    'isPrivate' => $accountInfo->isPrivate()
                ]
            ];
        }
        return $populatedPosts;
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
