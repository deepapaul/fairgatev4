<?php

/**
 * NewsletterSendCommand
 *
 * This command is used to send scheduled newsletters to receivers in spool
 *
 * @package    CommonUtilityBundle
 * @subpackage Command
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Common\UtilityBundle\Routing\FgRoutingListener;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This command is used to spool scheduled newsletter contacts
 *
 * @author pitsolutions.ch
 */
class NewsletterSendCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('newsletter:send_from_spool')
            ->setDescription('Insert newsletter receivers to fg_mail_message table')
            ->addOption('cron-instance', null, InputOption::VALUE_REQUIRED)
            ->addOption('message-limit', null, InputOption::VALUE_REQUIRED)
            ->addOption('time-limit', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = 0;
        $time = time();
        $timeMinute = date("i");
        $order = $timeMinute % 2;
        $cronInstance = $input->getOption('cron-instance');
        $messageLimit = $input->getOption('message-limit');
        $timeLimit = $input->getOption('time-limit');
        $container = $this->getContainer();
        $doctrine = $container->get('doctrine');
        $em = $doctrine->getManager();
        $translator = $container->get('translator');
        $mailer = $container->get('mailer');
        $conn = $em->getConnection();
        $returnPath = $container->getParameter('mailer_bounce_email');
        $rootPath = FgUtility::getRootPath($container);
        $mailerObjects = $em->getRepository('CommonUtilityBundle:FgMailMessage')->getSpooledMessages($cronInstance, $order, $messageLimit);
        $output->writeln('########## CRON: SENDING FROM SPOOL TABLE STARTS ##########');
        if (count($mailerObjects) > 0) {
            $newsletterId = 0;
            $sendStatusUpdateBackend = array();
            $bouncedMails = array();
            $clubId = 0;
            foreach ($mailerObjects as $mail) {
                $id = $mail['id'];
                $contactId = $mail['contactIds'];
                $currentNewsletterId = $mail['newsletterId'];
                $salutation = $mail['salutation'];
                $singleContactId = explode(',', $contactId);
                $receiverLogId = $mail['receiverLogId'];
                if (($currentNewsletterId != '') && !is_null($currentNewsletterId)) {
                    if ($newsletterId != $currentNewsletterId) {
                        $newsletterId = $currentNewsletterId;
                        $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
                    }
                }
                if ($newsletterObj) {
                    $newsletterType = $newsletterObj->getNewsletterType();
                    $resendStatus = $newsletterObj->getResentStatus();
                    if ($clubId != $newsletterObj->getClub()->getId()) {
                        $clubId = $newsletterObj->getClub()->getId();
                        $club = new FgRoutingListener($container, null, $clubId, true);
                        $container->set('club', $club);
                    }
                    $clubDetails = $club->get('club_details');
                    $clubDefaultLang = $club->get('club_default_lang');
                    //set locale with respect to particular contact
                    $rowContactLocale = array(0 => array('id' => $singleContactId[0], 'default_lang' => $mail['corresLang'], 'default_system_lang' => $mail['systemLanguage']));
                    $container->get('contact')->setContactLocale($container, null, $rowContactLocale, true);
                    $sendDate = $mail['sendDate']->format(FgSettings::getPhpDateFormat());
                    $output->writeln('\n' . '****' . FgSettings::getPhpDateTimeFormat() . '---' . $sendDate . ' senddate\n');
                    $newsletterPublishType = $newsletterObj->getPublishType();

                    $body = $newsletterObj->getNewsletterContent();
                    $attachments = array();
                    if ($newsletterType == 'GENERAL') {
                        $unsubscribeLink = '';
                        if ($clubId != 1) {
                            $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
                            if ($newsletterPublishType == 'SUBSCRIPTION') {
                                $secretKey = $container->getParameter('secret');
                                $receiverLogObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')->find($receiverLogId);
                                if ($receiverLogObj) {
                                    if ($receiverLogObj->getContactId() != '') {
                                        $unsubscribeLink = 'type=contact&id=' . $receiverLogObj->getContactId() . '&key=' . $secretKey;
                                    } elseif ($receiverLogObj->getSubscriber()->getId() != '') {
                                        $unsubscribeLink = 'type=subscriber&id=' . $receiverLogObj->getSubscriber()->getId() . '&key=' . $secretKey;
                                    }
                                    $unsubscribeLink = FgUtility::generateUrlForHost($container, $container->get('club')->get('url_identifier'), 'nl_newsletter_unsubscription_page', $checkClubHasDomain, array('encodings' => base64_encode($unsubscribeLink)));
                                    $unsubscribeLink = '<a style="color:#428bca; text-decoration: none" href="' . $unsubscribeLink . '"><u>' . $translator->trans("UNSUBSCRIBE_NEWSLETTER") . '</u></a>';
                                }
                            }
                        }
                        $trans = array(
                            "@@#salutation#@@" => $salutation,
                            "@@#contents#@@" => $translator->trans('NEWSLETTER_CONTENTS'),
                            "@@#attachments#@@" => $translator->trans('NL_ATTACHMENTS'),
                            '@@#poweredBy#@@' => $translator->trans('NL_MAILING_POWERED_BY'),
                            "mailTrackId" => $receiverLogId,
                            "@@#unsubscribeLink#@@" => $unsubscribeLink,
                            "@@#sendDate#@@" => $sendDate
                        );
                        $body = strtr($body, $trans);
                    } else {
                        $documents = $em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleDocuments')->getAttachmentsOfSimpleMail($newsletterId);
                        foreach ($documents as $doc) {
                            if ($doc['docType'] == 'APPEND') {
                                //TO BE DONE
                            } elseif (!empty($doc['filename'])) {
                                $communicationUploadFolder = FgUtility::getUploadFilePath($clubId, 'communication');
                                $filePath = $rootPath . "/$communicationUploadFolder/";

                                $fileName = $doc['filename'] ? $doc['filename'] : $doc['title'];
                                $file = $filePath . $fileName;
                                if (is_file($file) && file_exists($file)) {
                                    $attachments[$fileName] = $file;
                                }
                            }
                        }
                        $clubLogo = ($clubDetails[$mail['corresLang']]['logo'] !== '') ? $clubDetails[$mail['corresLang']]['logo'] : (($clubDetails[$clubDefaultLang]['logo'] !== '') ? $clubDetails[$clubDefaultLang]['logo'] : '');
                        $clubTitle = ($clubDetails[$mail['corresLang']]['title'] !== '') ? $clubDetails[$mail['corresLang']]['title'] : (($clubDetails[$clubDefaultLang]['title'] !== '') ? $clubDetails[$clubDefaultLang]['title'] : '');
                        $baseUrlArr = FgUtility::generateUrlForCkeditor($container, $clubId, 1);
                        $clubLogoUrl = '';
                        if (file_exists($rootPath . '/uploads/' . $clubId . '/admin/clublogo/' . $clubLogo)) {
                            $clubLogoUrl = $baseUrlArr['baseUrl'] . '/uploads/' . $clubId . '/admin/clublogo/' . $clubLogo;
                        }
                        if ($clubLogoUrl !== '') {
                            $clubHeading = '<table class="row" cellpadding="0" cellspacing="0" border="0" style="width:100%;"><tr><td style="padding-bottom:12px; vertical-align: middle; width: 1%;"><img style="max-width: 100px; padding-left: 1px;" src="'.$clubLogoUrl.'" /></td><td style="width: 28px; "></td><td style="font-family: \'Helvetica\', \'Arial\', sans-serif; vertical-align: middle;"><h1 style="margin: 0; font-size: 30px; font-weight: 500; margin-top: -4px;">'.$clubTitle.'</h1></td></tr></table>';
                        } else {
                            $clubHeading = '<table class="row" cellpadding="0" cellspacing="0" border="0" style="width:100%; table-layout: fixed;"><tr><td style="font-family: \'Helvetica\', \'Arial\', sans-serif; vertical-align: middle;"><h1 style="margin: 0; font-size: 30px; font-weight: 500; margin-top: -4px;">'.$clubTitle.'</h1></td></tr></table>';                                                        
                        }
                        $trans = array(
                            "@@#salutation#@@" => $salutation,
                            "@@#attachments#@@" => $translator->trans('NL_ATTACHMENTS'),
                            '@@#poweredBy#@@' => $translator->trans('NL_MAILING_POWERED_BY'),
                            "mailTrackId" => $receiverLogId,
                            "@@#sendDate#@@" => $sendDate,
                            "@@#clubLogoNTitle#@@" => $clubHeading
                        );
                        $body = strtr($body, $trans);
                    }
                    $message = null;
                    $sender_name = $newsletterObj->getSenderName();
                    $sender_email = $newsletterObj->getSenderEmail();
                    $send_date = $newsletterObj->getSendDate();
                    $from = array($sender_email => $sender_name);
                    $recepient = array($mail['email']);
                    $subject = $newsletterObj->getSubject();
                    $error = '';
                    try {
                        $message = \Swift_Message::newInstance()
                            ->setSubject($subject)
                            ->setFrom($from)
                            ->setTo($recepient)
                            ->setBody($body, 'text/html')
                            ->setReturnPath($returnPath)
                            ->setPriority(3)
                            ->setCharset('utf-8');
                        $msgId = $message->getHeaders()->get('Message-ID');
                        $msgId->setId($receiverLogId . '@fairgate.com');

                        $numSent = $mailer->send($message);
                        if ($numSent > 0) {
                            $count += $numSent;
                        }
                        $message->__destruct();
                        $message = null;
                        $sendStatusUpdateBackend[] = $receiverLogId;
                        if ($resendStatus == 2) {
                            $bouncedMails[$newsletterId] = $newsletterId;
                        }
                    }
                    /* If the emaild id is not RFC compliant */ catch (\Swift_RfcComplianceException $e) {
                        $error = 'Swift_RfcComplianceException. Recepients : ' . implode(',', $recepient);
                    }
                    /* If there is any problem with file attachments */ catch (\Swift_IoException $e) {
                        $error = 'Swift_IoException. $attachments : ' . implode(',', $attachments);
                    }
                    /* Other exceptions */ catch (Exception $e) {
                        $error = $e;
                    }
                    /* If any exception is caught */
                    if ($error != '') {
                        $output->writeln('\nError :: ' . $error);
                        $output->writeln('\nNewsletter_id : ' . $newsletterId . ', Contact_id : ' . $contactId . ', Recepients : ' . implode(',', $recepient) . '\n');
                    }
                    $object = $em->getRepository('CommonUtilityBundle:FgMailMessage')->find($id);
                    $em->remove($object);
                }
                $em->flush();
                if ($timeLimit && (time() - $time) >= $timeLimit) {
                    $output->writeln('\n' . $timeLimit . ' Time over\n');
                    break;
                }
            }
            $output->writeln('\n****Send ' . $count . ' Newsletters****\n');
            $output->writeln($container->get('router')->generate('nl_newsletter_unsubscription_page', array('encodings' => 'test'), UrlGeneratorInterface::ABSOLUTE_PATH));
            if (count($sendStatusUpdateBackend) > 0) {
                $receiverLogIds = implode(',', $sendStatusUpdateBackend);
                $updateQry = "UPDATE `fg_cn_newsletter_receiver_log` SET `is_sent` = 1, `is_bounced`=0, `is_email_changed`=0 WHERE `id` IN (" . $receiverLogIds . ")";
                $conn->executeQuery($updateQry);
            }
            if (count($bouncedMails) > 0) {
                $bouncedNewsletterIds = implode(',', $bouncedMails);
                $updateQry = "UPDATE `fg_cn_newsletter` SET `resent_status`=0 WHERE `id` IN (" . $bouncedNewsletterIds . ")";
                $conn->executeQuery($updateQry);
            }
        }
        $output->writeln('########## CRON: SENDING FROM SPOOL TABLE ENDS ##########');
    }
}