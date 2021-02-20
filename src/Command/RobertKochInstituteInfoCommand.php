<?php

namespace App\Command;

use App\Controller\RobertKochInstituteController;
use App\Entity\RobertKochInstitute;
use App\Service\DataFetchers\RobertKochInstituteDataFetcher;
use App\Service\External\NotifierProxyLoggerService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TypeError;

class RobertKochInstituteInfoCommand extends Command
{
    protected static $defaultName        = 'cron:robert-koch-institute-info-command';
    protected static $defaultDescription = 'Send small info about Corona risc area group change';

    /**
     * @var RobertKochInstituteDataFetcher $robertKochInstituteDataFetcher
     */
    private RobertKochInstituteDataFetcher $robertKochInstituteDataFetcher;

    /**
     * @var RobertKochInstituteController $robertKochInstituteController
     */
    private RobertKochInstituteController $robertKochInstituteController;

    private NotifierProxyLoggerService $notifierProxyLoggerService;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * RobertKochInstituteInfoCommand constructor.
     *
     * @param RobertKochInstituteDataFetcher $robertKochInstituteDataFetcher
     * @param RobertKochInstituteController $robertKochInstituteController
     * @param NotifierProxyLoggerService $notifierProxyLoggerService
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        RobertKochInstituteDataFetcher $robertKochInstituteDataFetcher,
        RobertKochInstituteController  $robertKochInstituteController,
        NotifierProxyLoggerService     $notifierProxyLoggerService,
        LoggerInterface                $logger,
        string $name = null
    )
    {
        parent::__construct($name);
        $this->robertKochInstituteDataFetcher = $robertKochInstituteDataFetcher;
        $this->robertKochInstituteController  = $robertKochInstituteController;
        $this->notifierProxyLoggerService     = $notifierProxyLoggerService;
        $this->logger                         = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try{
            $lastCreatedEntity                       = $this->robertKochInstituteController->getLastCreatedEntity();
            $robertKochInstituteEntityForPageChanges = $this->getRobertKochInstituteEntityForPageChanges($lastCreatedEntity);

            if( is_null($robertKochInstituteEntityForPageChanges) ){
                $this->logger->info("No changes has been done since last crawl");
                return Command::SUCCESS;
            }

            $response           = $this->notifierProxyLoggerService->insertDiscordMessageForRobertKochInstituteRiscAreaLastUpdateDate($robertKochInstituteEntityForPageChanges, $lastCreatedEntity);
            $responseCode       = $response->getCode();

            if( $responseCode >= 300 ){
                throw new Exception("Got {$responseCode} from NPL, request was not handled correctly");
            }
        }catch(Exception | TypeError $e){
            $this->logger->critical("Exception was thrown while trying to send the request to the NPL", [
                "info"             => __CLASS__ . "::" . __FUNCTION__,
                "exceptionCode"    => $e->getCode(),
                "exceptionMessage" => $e->getMessage(),
            ]);
        }

        return Command::SUCCESS;
    }

    /**
     * @param ?RobertKochInstitute $lastCreatedEntity
     * @return RobertKochInstitute|null
     * @throws GuzzleException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function getRobertKochInstituteEntityForPageChanges(?RobertKochInstitute $lastCreatedEntity): ?RobertKochInstitute
    {
        $currentArticleFetchedChangeDate           = $this->robertKochInstituteDataFetcher->crawlLastArticleChangeDate();
        $currentArticleChangeDateTimeForComparison = (
                is_null($currentArticleFetchedChangeDate)
            ?   null
            :   $currentArticleFetchedChangeDate->format("Y-m-d H:i:s")
        );

        $currentPageContentHash              = $this->robertKochInstituteDataFetcher->calculatePageContentHash();
        $lastCreatedEntityPageUpdateDateTime = (
            (
                    is_null($lastCreatedEntity)
                ||  is_null($lastCreatedEntity->getLastPageUpdateDateTime())
            )
            ?   null
            :   $lastCreatedEntity->getLastPageUpdateDateTime()->format("Y-m-d H:i:s")
        );

        if(
                empty($lastCreatedEntity)
            ||  $lastCreatedEntityPageUpdateDateTime     !== $currentArticleChangeDateTimeForComparison
            ||  $lastCreatedEntity->getPageContentHash() !== $currentPageContentHash
        ){
            $robertKochInstitute = new RobertKochInstitute();
            $robertKochInstitute->setLastPageUpdateDateTime($currentArticleFetchedChangeDate);
            $robertKochInstitute->setPageContentHash($currentPageContentHash);

            $this->robertKochInstituteController->save($robertKochInstitute);

            return $robertKochInstitute;
        }

        return null;
    }
}
