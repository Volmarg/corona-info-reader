<?php
namespace App\Service\External;

use App\DTO\Discord\DiscordMessageDTO;
use App\Entity\RobertKochInstitute;
use App\NotifierProxyLoggerBridge;
use App\Request\Discord\InsertDiscordMessageRequest;
use App\Response\Discord\InsertDiscordMessageResponse;
use App\Service\ConfigLoaders\ConfigLoaderSystem;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

/**
 * Handles communication with NPL
 *
 * Class NotifierProxyLoggerService
 * @package App\Services\External
 */
class NotifierProxyLoggerService
{

    const TITLE_ROBERT_KOCH_INSTITUTE_RISC_AREA_UPDATE = "Robert Koch institute Risc area list has been update";

    /**
     * @var NotifierProxyLoggerBridge $notifierProxyLoggerBridge
     */
    private NotifierProxyLoggerBridge $notifierProxyLoggerBridge;

    /**
     * @var ConfigLoaderSystem $configLoaderSystem
     */
    private ConfigLoaderSystem $configLoaderSystem;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * NotifierProxyLoggerService constructor.
     *
     * @param NotifierProxyLoggerBridge $notifierProxyLoggerBridge
     * @param ConfigLoaderSystem $configLoaderSystem
     * @param LoggerInterface $logger
     */
    public function __construct(NotifierProxyLoggerBridge $notifierProxyLoggerBridge, ConfigLoaderSystem $configLoaderSystem, LoggerInterface $logger)
    {
        $this->logger                    = $logger;
        $this->configLoaderSystem        = $configLoaderSystem;
        $this->notifierProxyLoggerBridge = $notifierProxyLoggerBridge;
    }

    /**
     * Will use insert single discord message to the queue in NPL
     *
     * @param RobertKochInstitute $robertKochInstituteForPageChanges
     * @param RobertKochInstitute|null $lastCreatedEntity
     * @return InsertDiscordMessageResponse
     * @throws GuzzleException
     * @throws Exception
     */
    public function insertDiscordMessageForRobertKochInstituteRiscAreaLastUpdateDate(RobertKochInstitute $robertKochInstituteForPageChanges, ?RobertKochInstitute $lastCreatedEntity): InsertDiscordMessageResponse
    {
        try{
            $discordMessageDto = new DiscordMessageDTO();
            $request           = new InsertDiscordMessageRequest();

            $messageContent = $this->buildMessageContentForRobertKochInstituteRiscAreaUpdate($robertKochInstituteForPageChanges, $lastCreatedEntity);
            $discordMessageDto->setWebhookName(NotifierProxyLoggerBridge::WEBHOOK_NAME_ALL_NOTIFICATIONS);
            $discordMessageDto->setMessageTitle(self::TITLE_ROBERT_KOCH_INSTITUTE_RISC_AREA_UPDATE);
            $discordMessageDto->setMessageContent($messageContent);
            $discordMessageDto->setSource(NotifierProxyLoggerBridge::SOURCE_CIR);

            $request->setDiscordMessageDto($discordMessageDto);
            $response = $this->notifierProxyLoggerBridge->insertDiscordMessage($request);
        }catch(Exception $e){
            $this->logger->critical("Exception was thrown while sending discord message for RKI last update", [
                "exceptionMessage" => $e->getMessage(),
                "exceptionCode"    => $e->getCode(),
            ]);
            throw $e;
        }

        return $response;
    }

    /**
     * Will output string from the RKI entity, this is later on passed as message for NPL
     *
     * @param RobertKochInstitute $robertKochInstituteForPageChanges
     * @param ?RobertKochInstitute $lastCreatedEntity
     * @return string
     */
    private function buildMessageContentForRobertKochInstituteRiscAreaUpdate(RobertKochInstitute $robertKochInstituteForPageChanges, ?RobertKochInstitute $lastCreatedEntity): string
    {

        $pageHash = "";
        if( !empty($lastCreatedEntity) ){
            $pageHash = "Page content hash has has been Changed. Was: {$lastCreatedEntity->getPageContentHash()}, is: {$robertKochInstituteForPageChanges->getPageContentHash()}";
        }

        $articleLastChangeDate = (
            is_null($robertKochInstituteForPageChanges->getLastPageUpdateDateTime())
            ? "unknown"
            : $robertKochInstituteForPageChanges->getLastPageUpdateDateTime()->format('Y-m-d H:i:s')
        );

        $dateChangeString = "Article Last Change Date: {$articleLastChangeDate}";
        $outputString     = $dateChangeString . ", " . $pageHash;

        return $outputString;
    }

}