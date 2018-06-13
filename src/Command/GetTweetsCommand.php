<?php
declare(strict_types=1);


namespace SocialMediaRepository\Command;

use Selami\Console\Command as SelamiCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use SocialMediaRepository\Domain\Tweets\TweetService;

class GetTweetsCommand extends SelamiCommand
{
    protected function configure() : void
    {
        $this
            ->setName('get:tweet-texts')
            ->setDescription('Gets latest tweet texts of given username')
            ->setDefinition(
                [
                    new InputArgument('queryType', InputArgument::REQUIRED),
                    new InputArgument('keyword', InputArgument::REQUIRED),
                ]
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {

        $queryType = $input->getArgument('queryType');
        $keyword = $input->getArgument('keyword');
        $output->writeln(date('Y-m-d H:i:s').': Getting tweet posts for ' . $queryType . ':'. $keyword);
        $output->writeln('------------------------------------');
        $posts = $this->getTweets($queryType, $keyword);
        $output->writeln(date('Y-m-d H:i:s').': DONE: ');

       /* $config = $this->container->get('config');
        $keyword = $input->getArgument('keyword');

        $output->writeln(date('Y-m-d H:i:s').': Getting tweets for' . $username);
        $output->writeln('------------------------------------');

        $tweets = (new TweetService($config['twitter']))
            ->getTweetTextsByUsername(str_replace('@', '', $username), 50);

        $fileName = $config['storage']['tmp'].'/'.$username.'.json';
        file_put_contents($fileName, json_encode($tweets));
        $output->writeln(date('Y-m-d H:i:s').': DONE: ' . $fileName);*/
    }
    private function getTweets(string $queryType, string $keyword) : ?array
    {
        /**
         * @param \SocialMediaRepository\Domain\Instas\InstaService $instaService
         */
        $instaService = $this->container->get('TweetService');
        if ($queryType === 'user') {
            return $instaService->getTweetsByUsername($keyword);
        }
        return $instaService->getTweetsByHashtag($keyword, 50);
    }
}
