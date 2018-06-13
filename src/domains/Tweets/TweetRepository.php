<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Tweets;

use SocialMediaRepository\Domain;

class TweetRepository extends Domain\AbstractRepository
{

    private const PLATFORM_ID = 1;

    public function getPosts(string $hashtag, string $maxId)
    {
        return $this->getPostsByPlatformId(self::PLATFORM_ID, $hashtag, $maxId);
    }

    public function savePosts(string $hashtag, array $postData)
    {
        return $this->savePostsByPlatformId(self::PLATFORM_ID, $hashtag, $postData);
    }
}
