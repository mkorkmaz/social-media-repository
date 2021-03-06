<?php
declare(strict_types=1);

namespace SocialMediaRepository\Command;

use Selami\Console\Command as SelamiCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class GetInstasCommand extends SelamiCommand
{

    protected function configure() : void
    {
        $this
            ->setName('get:insta-posts')
            ->setDescription('Gets latest instagram posts of given username or hastag')
            ->setDefinition(
                [
                new InputArgument('queryType', InputArgument::REQUIRED),
                    new InputArgument('keyword', InputArgument::REQUIRED),
                    new InputArgument('output', InputArgument::OPTIONAL),

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
        $outputDir = $input->getArgument('output', '');
        $output->writeln(date('Y-m-d H:i:s').': Getting instagram posts for ' . $queryType . ':'. $keyword);
        $output->writeln('------------------------------------');
        $posts = $this->getInstas($queryType, $keyword, $outputDir);
        $output->writeln(date('Y-m-d H:i:s').': DONE: ');
    }

    /**
     * @param string $queryType
     * @param string $keyword
     * @return array|null
     * @throws \Exception
     */
    private function getInstas(string $queryType, string $keyword, string $outputDir) : ?array
    {
        /**
         * @param \SocialMediaRepository\Domain\Instas\InstaService $instaService
         */
        $instaService = $this->container->get('InstagramService');
        if ($queryType === 'user') {
            return $instaService->getInstasByUsername($keyword);
        }
        return $instaService->getInstasByHashtag($keyword, 50, $outputDir);
    }
}
