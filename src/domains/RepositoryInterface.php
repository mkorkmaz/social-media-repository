<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain;

use Soupmix\Base as Soupmix;

interface RepositoryInterface
{

    public function __construct(Soupmix $soupmix);

    public function getPosts(string $hashtag, string $maxId);

    public function savePost(string $hashtag, array $postData, ?string $outputDir);
}
