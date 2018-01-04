<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain;

use Soupmix\Base as Soupmix;
use MongoDB\BSON\ObjectId;

abstract class AbstractRepository implements RepositoryInterface
{
    protected $soupmix;

    protected const COLLECTION_NAME = 'posts';

    public function __construct(Soupmix $soupmix)
    {
        $this->soupmix = $soupmix;
    }

    /**
     * @param int    $platformId
     * @param string $hashtag
     * @param string $minId
     * @return mixed
     */
    protected function getPostsByPlatformId(int $platformId, string $hashtag, string $minId)
    {
        return $this->soupmix->find(
            self::COLLECTION_NAME,
            [
                '_platformId' => $platformId,
                '_hashtag' => $hashtag,
                '_id__gt' => new ObjectId($minId),
            ],
            null,
            ['_id' => 'asc']
        );
    }

    protected function savePostByPlatformId(int $platformId, string $hashtag, array $postData) : ?string
    {
        $postData['_platformId'] = $platformId;
        $postData['_hashtag'] = $hashtag;
        if ($this->doesPostExist($platformId, $hashtag, $postData['postId'])) {
            return null;
        }
        return $this->soupmix->insert(self::COLLECTION_NAME, $postData);
    }

    private function doesPostExist(int $platformId, string $hashtag, string $postId)
    {
        $check = $this->soupmix->find(
            self::COLLECTION_NAME,
            [
                '_platformId' => $platformId,
                '_hashtag' => $hashtag,
                'postId' => $postId
            ],
            ['_id'],
            null,
            0,
            1
        );
        if ($check['total'] === 0) {
            return false;
        }
        return true;
    }
}
