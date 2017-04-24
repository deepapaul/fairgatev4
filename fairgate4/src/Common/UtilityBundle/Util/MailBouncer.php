<?php
/**
 * MailBouncer is used for opening email account ,
 * parsing it and move the specific emails to another folder
 *
 */
namespace Common\UtilityBundle\Util;

class MailBouncer {
    public $conn;
    public $inbox;
    private $msg_cnt;
    // email login credentials
    private $server;  //= 'gmail.com';
    private $host; //   = '{imap.gmail.com:993/ssl}';
    private $user;  //= 'fgatebounce@gmail.com';
    private $pass;  //= 'pits123#';
    private $port;  // = 993; // adjust according to server settings

    public $folder = "inbox";
    private static $start_time = 0;
    private static $time_limit = 540;
    public $container;

    /*
     * Connect to the server
     */
    function __construct($container) {
        $this->container =  $container;
        $this->server = $this->container->getParameter('mailer_bounce_server');
        $this->port = $this->container->getParameter('mailer_bounce_server_port');
        $this->user = $this->container->getParameter('mailer_bounce_email');
        $this->pass = $this->container->getParameter('mailer_bounce_email_password');
        $this->host = '{'.$this->server.':'.$this->port.'/ssl/novalidate-cert}';
        $this->connect();
        self::$start_time = time();
    }
   
    /**
     * Close the server connection
     */
    function close() {
        $this->inbox = array();
        $this->msg_cnt = 0;
        imap_close($this->conn);
    }
  
    /**
     * Open the server connection
     */
    function connect() {
        $this->conn = imap_open($this->host , $this->user, $this->pass) or die('Cannot connect to Server: ' . print_r(imap_errors()));
    }

    /**
     * Move the message to a new folder
     * @param type $msg_index   Message index
     * @param type $folder      Folder name to move
     */
    function move($msg_index, $folder='INBOX.Processed') {
        imap_mail_move($this->conn, $msg_index, $folder);
    }
  
    /**
     * Read the inbox and move bounced mails to Folder 'Bounced Emails' and other mails to 'Other Emails'
     */
    function inbox() {
        $this->msg_cnt = imap_num_msg($this->conn);
        $this->msg_cnt = ($this->msg_cnt > 50) ? 50 : $this->msg_cnt;
        for($i = $this->msg_cnt; $i >=1; $i--) { 
            $current_time = time();
            if( ($current_time - self::$start_time) >= (self::$time_limit)  ) {
                echo "time limit exceeded";
                break;
            }
            $headerinfoObj = imap_headerinfo($this->conn, $i);
            //normally message-id of bounced emails exist in property in_reply_to
            if(property_exists($headerinfoObj, 'in_reply_to')) {                
                $mailHeader = $headerinfoObj->in_reply_to;
                $matches = array();
                if($mailHeader) {
                    \preg_match('~<(.*?)@~', $mailHeader, $matches);
                    $mailId = $matches[1];
                    $this->updateLog($mailId, $i);
                }                
            } else {
                //if property in_reply_to is not existing message-id is extracted from mail body
                $bodyHeader = imap_fetchbody($this->conn, $i, 3);
                \preg_match('~(Message-ID: <)(.*?)(@fairgate.com>)~', $bodyHeader, $matches);                 
                if(count($matches) > 1) {
                    $mailId = $matches[2]; 
                    $this->updateLog($mailId, $i);
                } else {
                    $this->move($i, "Other Emails");
                } 
            }
        }
        //for deleting moved mails from moved folder
        imap_expunge($this->conn);
    }
    
    /*
     * Method to update log in database and move the mail to Bounced email folder
     * @param $mailId primary id in table newsletter_receiver_log
     * @param $index mail index number
     */
    private function updateLog($mailId, $index) {
        //To fetch a section of mail body
        $mailMessage = htmlspecialchars(imap_fetchbody($this->conn, $index, 1));
        $em = $this->container->get('doctrine')->getManager();
        $em->getRepository('CommonUtilityBundle:FgCnNewsletterReceiverLog')
                        ->updateBouncedEmailStatus($mailId, $mailMessage);
        $this->move($index, "Bounced Emails");
    }

    /*
     *  Reopen connection and read messages from spam folder
     */
    function spam() {
        if($this->folder == "inbox") {
            $this->folder = "Junk Email";
            imap_reopen($this->conn, $this->host.'Junk Email');
            $this->inbox();
        }
    }

}
