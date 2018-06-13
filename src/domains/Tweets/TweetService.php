<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Tweets;

use TwitterAPIExchange;

class TweetService
{
    private $twitterHandle;

    private $repository;

    /**
     * TweetService constructor.
     *
     * @param TweetRepository $repository
     * @param array           $config
     */
    public function __construct(TweetRepository $repository, array $config)
    {
        $this->repository = $repository;
        $this->twitterHandle = new TwitterAPIExchange($config['twitter']);
    }

    public function getPosts(string $hastag, string $maxId): array
    {
        return $this->repository->getPosts($hastag, $maxId);
    }

    /**
     * @param string $username
     * @param int    $limit
     * @return array
     * @throws \Exception
     */
    public function getTweetsByUsername(string $username, int $limit = 200) : array
    {
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $requestMethod = 'GET';
        $parameters = array(
            'screen_name' => $username,
            'exclude_replies' => true,
            'count' => $limit,
            'include_rts' => false
        );
        $response = $this->twitterHandle->setGetfield(http_build_query($parameters))
            ->buildOauth($url, $requestMethod)
            ->performRequest();
        return json_decode($response, true);
    }

    /**
     * @param string $username
     * @param int    $limit
     * @return array
     * @throws \Exception
     */
    public function getTweetTextsByUsername(string $username, int $limit = 200) : array
    {
        $tweets = $this->getTweetsByUsername($username);

        if (array_key_exists('errors', $tweets)) {
            return ['404' => 1];
        }
        $texts = [];
        $count = 0;
        foreach ($tweets as $key => $tweet) {
            $texts[] = $tweet['text'];
            $count++;
            if ($count > $limit) {
                break;
            }
        }
        return $texts;
    }
}
