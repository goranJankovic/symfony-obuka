<?php

namespace App\Command;

use App\Document\Ad;
use App\Util\UnixHelper;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:get-last-months-ads',
    description: 'Add a short description for your command',
)]
class GetLastMonthsAdsCommand extends Command
{
    public function __construct(
        private readonly DocumentManager $documentManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $ads = $this->documentManager->getRepository(Ad::class)->findLastMonthsAds();
        $lastMonthUnix = UnixHelper::getLastMonthUnix();

        $filename = 'ads.csv';
        $file = fopen($filename, 'w');

        if ($file === false){
            $io->error('Unable to open file!');
            return Command::FAILURE;
        }

        foreach ($ads as $ad) {
            fputcsv($file, [
                $ad->getName(),
                $ad->getDescription(),
                $ad->getUrl(),
                $ad->getDateTime()
            ]);
        }

        fclose($file);

        $io->success('Generated file');

        return Command::SUCCESS;
    }
}
