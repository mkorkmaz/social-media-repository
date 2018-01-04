<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Instas;

use SocialMediaRepository\Domain;

class InstaRepository extends Domain\AbstractRepository
{
    private const PLATFORM_ID = 2;

    public function getPosts(string $hashtag, string $minId)
    {
        return $this->getPostsByPlatformId(self::PLATFORM_ID, $hashtag, $minId);
    }

    public function savePost(string $hashtag, array $postData)
    {
        return $this->savePostByPlatformId(self::PLATFORM_ID, $hashtag, $postData);
    }
}
