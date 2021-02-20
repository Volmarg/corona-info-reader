<?php


namespace App\Service\DataFetchers;


use App\Service\GuzzleService;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;
use TypeError;

class RobertKochInstituteDataFetcher
{
    const RKI_URL_RISC_AREAS                         = "https://www.rki.de/DE/Content/InfAZ/N/Neuartiges_Coronavirus/Risikogebiete_neu.html";
    const RKI_RISC_AREAS_REGEX_LAST_CHANGE_DATE_TIME = '(?<DATE>[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4})(.*)(?<TIME>[0-9]{2}:[0-9]{2})';

    /**
     * @var GuzzleService $guzzleHttpService
     */
    private GuzzleService $guzzleHttpService;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    public function __construct(GuzzleService $guzzleHttpService, LoggerInterface $logger)
    {
        $this->guzzleHttpService = $guzzleHttpService;
        $this->logger            = $logger;
    }

    /**
     * Attempt to obtain information about last article change date
     *
     * @return DateTime
     * @throws GuzzleException
     */
    public function crawlLastArticleChangeDate(): ?DateTime
    {
        try{
            $pageContent = $this->geRkiRiscAreasPageContent();
            $crawler     = new Crawler($pageContent);

            try{
                $lastChangeDateText = $crawler->filter('.subheadline')->text();
            }catch(Exception $e){
                $this->logger->critical("Seems like the selector used for getting last page change date is no longer present!", [
                    "exceptionMessage" => $e->getMessage(),
                    "exceptionCode"    => $e->getCode(),
                ]);
                return null;
            }
            preg_match('#' . self::RKI_RISC_AREAS_REGEX_LAST_CHANGE_DATE_TIME . '#', $lastChangeDateText, $matches);
            $lastChangeDateString = $matches['DATE'];
            $lastChangeTimeString = $matches['TIME'];

            $lastChangeDateTimeString = $lastChangeDateString . " " . $lastChangeTimeString;
            $lastChangeDateTime       = DateTime::createFromFormat("d.m.Y H:i", $lastChangeDateTimeString);
        }catch(Exception | TypeError $e){
            $this->logger->critical("Exception was thrown while checking the RKI risc area article last change date", [
                "exceptionCode"    => $e->getCode(),
                "exceptionMessage" => $e->getMessage(),
            ]);

            throw $e;
        }

        return $lastChangeDateTime;
    }

    /**
     * Calculates the hash value of page content in case of page content being changed without the article change date
     *
     * @return string
     * @throws GuzzleException
     */
    public function calculatePageContentHash(): string
    {
        try{
            $pageContent     = $this->geRkiRiscAreasPageContent();
            $crawler         = new Crawler($pageContent);
            $pageBodyContent = $crawler->filter('body')->text();
            $hash            = md5($pageBodyContent);

        }catch(Exception | TypeError $e){
            $this->logger->critical("Exception was thrown while calculating RKI risc area page content hash", [
                "exceptionCode"    => $e->getCode(),
                "exceptionMessage" => $e->getMessage(),
            ]);

            throw $e;
        }

        return $hash;
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    private function geRkiRiscAreasPageContent(): string
    {
        $this->validateRkiRiscAreasResponseStatusCode();

        $pageContent = $this->guzzleHttpService->getPageContent(self::RKI_URL_RISC_AREAS);
        return $pageContent;
    }

    /**
     * Will fetch the response code for the RKI, in case of some issues with RKI PAGE
     * @throws GuzzleException
     * @throws Exception
     */
    private function validateRkiRiscAreasResponseStatusCode(): void
    {
        $pageStatusCode = $this->guzzleHttpService->getStatusCodeForGetCallOnUrl(self::RKI_URL_RISC_AREAS);

        if( $pageStatusCode >= 300 || $pageStatusCode < 200 ){
            $message = "The RKI risc area returned undesired status code: {$pageStatusCode}";
            $this->logger->critical($message);
            throw new Exception($message);
        }

    }

}