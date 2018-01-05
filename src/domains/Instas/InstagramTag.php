<?php
declare(strict_types=1);

namespace SocialMediaRepository\Domain\Instas;

use GuzzleHttp;

class InstagramTag
{
    private const JSON_OBJECT_AS_ARRAY = true;

    private $url;

    private $browserId = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:58.0) Gecko/20100101 Firefox/58.0';

    public function __construct(string $tagName)
    {
        $this->url = str_replace('{tag}', $tagName, 'https://www.instagram.com/explore/tags/{tag}/');
    }

    public function getPosts(int $limit=10)
    {
        $client = new GuzzleHttp\Client(['User-Agent' => $this->browserId]);
        $response = $client->get($this->url);
        $body = $response->getBody()->getContents();
        preg_match('#<script type="text/javascript">window._sharedData =(.*?);</script>#msi', $body, $match);
        $data = json_decode($match[1], self::JSON_OBJECT_AS_ARRAY);
        $tagPosts = $data['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
        $posts = [];
        $count = 0;
        foreach($tagPosts as $tagPost)
        {
            $posts[]= $this->getPost($tagPost['node']['shortcode']);
            $count++;
            if ($count>=$limit) {
                break;
            }
        }
        return $posts;
    }

    /**
     * @param string $shortCode
     * @return mixed
     * @throws \RuntimeException
     */
    public function getPost(string $shortCode)
    {
        echo('Getting post :' . $shortCode) . PHP_EOL;
        $url = str_replace('{code}', $shortCode, 'https://www.instagram.com/p/{code}');
        $client = new GuzzleHttp\Client(['User-Agent' => $this->browserId]);
        $response = $client->get($url);
        $body = $response->getBody()->getContents();
        preg_match('#<script type="text/javascript">window._sharedData =(.*?);</script>#msi', $body, $match);
        $data = json_decode($match[1], self::JSON_OBJECT_AS_ARRAY);
        return $data['entry_data']['PostPage'][0]['graphql']['shortcode_media'];
    }


}