<?php
/* System contact field categories */
$categoryPersonal = 1;
$categoryCorrespondance = 2;
$categoryInvoice = 137;
$categoryCompany = 3;
$categoryCommunication = 6;
$container->setParameter('system_category_personal', $categoryPersonal);
$container->setParameter('system_category_address', $categoryCorrespondance);
$container->setParameter('system_category_company', $categoryCompany);
$container->setParameter('system_category_communication', $categoryCommunication);
$container->setParameter('system_category_invoice', $categoryInvoice);

$systemCategories = array($categoryPersonal, $categoryCorrespondance, $categoryCompany, $categoryCommunication, $categoryInvoice);
$container->setParameter('system_categories', $systemCategories);
/* System contact fields */
//PERSONAL
$salutaion = 1;
$firstName = 2;
$dob = 4;
$profilePictureTeam = 5;
$profilePictureClub = 21;
$lastName = 23;
$title = 70;
$gender = 72;
$nationality1 = 76;
$nationality2 = 107;
$correspondanceLang = 515;

$systemPersoanlFields = array($salutaion, $firstName, $dob, $profilePictureTeam, $profilePictureClub, $lastName, $title, $gender, $nationality1, $nationality2, $correspondanceLang);
$container->setParameter('system_personal_fields', $systemPersoanlFields);

/* Personal Category fields used for Both company and Single person */
$systmPersonaBothFields = array($salutaion, $firstName, $dob, $lastName, $title, $gender, $nationality1, $nationality2);
$container->setParameter('system_personal_both', $systmPersonaBothFields);

/* System contact personal fields */
$container->setParameter('system_field_salutaion', $salutaion);
$container->setParameter('system_field_firstname', $firstName);
$container->setParameter('system_field_dob', $dob);
$container->setParameter('system_field_team_picture', $profilePictureTeam);
$container->setParameter('system_field_communitypicture', $profilePictureClub);
$container->setParameter('system_field_lastname', $lastName);
$container->setParameter('system_field_title', $title);
$container->setParameter('system_field_gender', $gender);
$container->setParameter('system_field_nationality1', $nationality1);
$container->setParameter('system_field_nationality2', $nationality2);
$container->setParameter('system_field_corress_lang', $correspondanceLang);

//COREESPONADANCE ADDRESS
$correspondaceStrasse = 47;
$correspondaceOrt = 77;
$correspondaceKanton = 78;
$correspondacePlz = 79;
$correspondaceLand = 106;
$correspondacePostfach = 71785;
$systemCorresFields = array($correspondaceStrasse, $correspondaceOrt, $correspondaceKanton, $correspondacePlz, $correspondaceLand, $correspondacePostfach);
$container->setParameter('system_correspondance_fields', $systemCorresFields);
/* System contact coreespondance address fields */
$container->setParameter('system_field_corres_strasse', $correspondaceStrasse);
$container->setParameter('system_field_corres_ort', $correspondaceOrt);
$container->setParameter('system_field_corres_kanton', $correspondaceKanton);
$container->setParameter('system_field_corres_plz', $correspondacePlz);
$container->setParameter('system_field_corres_land', $correspondaceLand);
$container->setParameter('system_field_corres_postfach', $correspondacePostfach);

//INVOICE ADDRESS
$invoiceStrasse = 71873;
$invoiceOrt = 71875;
$invoiceKanton = 71876;
$invoicePlz = 71874;
$invoiceLand = 71877;
$invoicePostfach = 71878;
$systemInvoiceFields = array($invoiceStrasse, $invoiceOrt, $invoiceKanton, $invoicePlz, $invoiceLand, $invoicePostfach);
$container->setParameter('system_invoice_fields', $systemInvoiceFields);
/* System contact invoice address fields */
$container->setParameter('system_field_invoice_strasse', $invoiceStrasse);
$container->setParameter('system_field_invoice_ort', $invoiceOrt);
$container->setParameter('system_field_invoice_kanton', $invoiceKanton);
$container->setParameter('system_field_invoice_plz', $invoicePlz);
$container->setParameter('system_field_invoice_land', $invoiceLand);
$container->setParameter('system_field_invoice_postfach', $invoicePostfach);

//COMPANY
$companyname = 9;
$companyLogo = 68;
$systemCompanyFields = array($companyname, $companyLogo);
$container->setParameter('system_company_fields', $systemCompanyFields);
/* System contact company fields */
$container->setParameter('system_field_companyname', $companyname);
$container->setParameter('system_field_companylogo', $companyLogo);

//COMMUNICATION
$primaryEmail = 3;
$mobile1 = 86;
$mobile2 = 87;
$parentEmail1 = 92;
$parentEmail2 = 108;
$website = 1002;
$systemCommunicationFields = array($primaryEmail, $mobile1, $mobile2, $parentEmail1, $parentEmail2, $website);
$container->setParameter('system_communication_fields', $systemCommunicationFields);
/* System contact communication fields */
$container->setParameter('system_field_primaryemail', $primaryEmail);
$container->setParameter('system_field_mobile1', $mobile1);
$container->setParameter('system_field_mobile2', $mobile2);
$container->setParameter('system_field_parentemail1', $parentEmail1);
$container->setParameter('system_field_parentemail2', $parentEmail2);
$container->setParameter('system_field_website', $website);

//All system fields
$systemFields = array_merge($systemPersoanlFields, $systemCorresFields, $systemInvoiceFields, $systemCompanyFields, $systemCommunicationFields);
$container->setParameter('system_fields', $systemFields);

//All country fields
$countryFields = array($nationality1, $nationality2, $correspondaceLand, $invoiceLand);
$container->setParameter('country_fields', $countryFields);

//APC Caching related parameters
$container->setParameter('cache_expire_time', '86400');
$container->setParameter('caching_enabled', true);
$container->setParameter('cache_apc_domain_name', 'pits');

//Terminology Term
$container->setParameter('singular', 's');
$container->setParameter('plural', 'p');
$container->setParameter('federation', 'federation');

//image resolutions  for contact
$container->setParameter('largeImageWidth', 640);
$container->setParameter('largeImageHeight', 640);
$container->setParameter('templatelargeImageWidth', 560);
$container->setParameter('templateHeight', 280);

//id for contact poicrures
$container->setParameter('systemTeamPicture', 5);
$container->setParameter('systemCommunityPicture', 21);
$container->setParameter('systemcompanyLogo', 68);
//ends
$container->setParameter('singleline_max_length', 160);
$container->setParameter('multiline_max_length', 4000);
$container->setParameter('max_file_size', 1048576);
$imageMimeTypes = array('image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/ico', 'image/x-icon', 'image/x-bmp', 'image/x-bmp',
    'image/x-bitmap',
    'image/x-xbitmap',
    'image/x-win-bitmap',
    'image/x-windows-bmp',
    'image/ms-bmp',
    'image/x-ms-bmp',
    'image/svg+xml');

$container->setParameter('image_mime_types', $imageMimeTypes);
$otherMimeTypes = array();
$otherMimeTypes[] = 'text/calendar';
$otherMimeTypes[] = 'application/pdf';
$otherMimeTypes[] = 'application/x-empty';
$otherMimeTypes[] = 'text/ecmascript';
$otherMimeTypes[] = 'application/octet-stream';
$otherMimeTypes[] = 'application/excel';
$otherMimeTypes[] = 'text/anytext';
$otherMimeTypes[] = 'text/comma-separated-values';
$otherMimeTypes[] = 'text/tab-separated-values';
$otherMimeTypes[] = 'application/x-zip';
$otherMimeTypes[] = 'application/x-gzip';
$otherMimeTypes[] = 'text/css';
$otherMimeTypes[] = 'application/x-compressed';
$otherMimeTypes[] = 'application/rtf';
$otherMimeTypes[] = 'text/richtext';
$otherMimeTypes[] = 'application/zip';
$otherMimeTypes[] = 'application/msword';
$otherMimeTypes[] = 'application/vnd.msexcel';
$otherMimeTypes[] = 'application/vnd.ms-excel';
$otherMimeTypes[] = 'application/vnd.ms-powerpoint';
$otherMimeTypes[] = 'application/oda';
$otherMimeTypes[] = 'text/csv';
$otherMimeTypes[] = 'application/csv';
$otherMimeTypes[] = 'application/ms-excel';
$otherMimeTypes[] = 'application/txt';
$otherMimeTypes[] = 'text/plain';
$otherMimeTypes[] = 'text/x-comma-separated-values';
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.spreadsheet';
$otherMimeTypes[] = 'image/vnd.microsoft.icon';
$otherMimeTypes[] = 'video/x-ms-wmv';

// http: filext.com/faq/office_mime_types.php
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
$otherMimeTypes[] = 'application/vnd.ms-word.document.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.ms-word.template.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
$otherMimeTypes[] = 'application/vnd.ms-excel.sheet.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.ms-excel.template.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.ms-excel.addin.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.ms-excel.sheet.binary.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.template';
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
$otherMimeTypes[] = 'application/vnd.ms-powerpoint.addin.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.ms-powerpoint.presentation.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.ms-powerpoint.template.macroEnabled.12';
$otherMimeTypes[] = 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12';
//Mime Content Types used in OpenOffice.org2.0 / StarOffice 8 and later
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.text';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.text-template';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.text-web';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.text-master';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.graphics';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.graphics-template';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.presentation';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.presentation-template';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.chart';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.formula';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.database';
$otherMimeTypes[] = 'application/vnd.oasis.opendocument.image';
$otherMimeTypes[] = 'application/vnd.openofficeorg.extension';

$otherMimeTypes[] = 'application/x-shockwave-flash';
$otherMimeTypes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
$otherMimeTypes[] = 'application/vnd.ms-excel.12';

$unlimitedMimeTypes = array_merge($imageMimeTypes, $otherMimeTypes);
$container->setParameter('unlimited_mime_types', $unlimitedMimeTypes);
$defaultTableSettings = array(
    '1' => array('id' => "$primaryEmail", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_$primaryEmail"),
    '2' => array('id' => "47", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_47"),
    '3' => array('id' => "79", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_79"),
    '4' => array('id' => "77", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_77"),
    '5' => array('id' => "86", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_86")
);
$defaultTableSettings = json_encode($defaultTableSettings);
$container->setParameter('default_table_settings', $defaultTableSettings);
$fedMembers = "FED_MEMBERS";
$subFed = "subfed";
$defaultClubTableSettings = array(
    '1' => array('id' => "clubname", 'type' => 'CD', 'club_id' => '1', 'name' => "clubname"),
    '2' => array('id' => "$fedMembers", 'type' => 'SI', 'club_id' => '1', 'name' => "SI" . $fedMembers),
    '3' => array('id' => "$subFed", 'type' => 'CO', 'club_id' => '1', 'name' => "CO$subFed"),
    '4' => array('id' => "C_street", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_C_street"),
    '5' => array('id' => "C_zipcode", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_C_zipcode"),
    '6' => array('id' => "C_city", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_C_city"),
    '7' => array('id' => "website", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_website")
);
$defaultClubTableSettings = json_encode($defaultClubTableSettings);
$container->setParameter('default_club_table_settings', $defaultClubTableSettings);
//news rss feed url
$feed_url = "http://v4blog.fairgate.ch/?feed=rss2";
$news_blog_url = "http://v4blog.fairgate.ch";
$container->setParameter('fgV4_news_rss_feed_url', $feed_url);
$container->setParameter('fgV4_blog_url', $news_blog_url);
$actualHost = 'https://mein.fairgate.ch'; //'http://fgv4.fairgate.ch';  for testing
$container->setParameter('hostName', $actualHost);
$container->setParameter('mailer_bounce_email', 'bounce@fairgate.ch');
$baseUrl = 'https://mein.fairgate.ch';
$container->setParameter('base_url', $baseUrl);
$container->setParameter('mailer_bounce_email_password', 'Hofa95211');
$container->setParameter('mailer_bounce_server', 'outlook.office365.com');
$container->setParameter('mailer_bounce_server_port', '993');
$defaultSponsorTableSettings = array(
    '1' => array('id' => "$correspondaceStrasse", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_" . $correspondaceStrasse),
    '2' => array('id' => "$correspondacePostfach", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_" . $correspondacePostfach),
    '3' => array('id' => "$correspondaceOrt", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_" . $correspondaceOrt),
    '4' => array('id' => "active_assignments", 'type' => 'SA', 'club_id' => '1', 'name' => "SAactive_assignments"),
    '5' => array('id' => "future_assignments", 'type' => 'SA', 'club_id' => '1', 'name' => "SAfuture_assignments"),
    '6' => array('id' => "past_assignments", 'type' => 'SA', 'club_id' => '1', 'name' => "SApast_assignments")
);
$defaultSponsorTableSettings = json_encode($defaultSponsorTableSettings);
$container->setParameter('default_sponsor_table_settings', $defaultSponsorTableSettings);
$container->setParameter('cache_lifetime', '2592000'); //valid for 30days
//connection relation ids
$relationIdsArr = array('parent' => 2, 'child' => 3);
$container->setParameter('relationIds', $relationIdsArr);
$roleUserRightsArr = array(9 => 'ROLE_GROUP_ADMIN', 10 => 'ROLE_CONTACT_ADMIN', 11 => 'ROLE_FORUM_ADMIN', 12 => 'ROLE_DOCUMENT_ADMIN', 13 => 'ROLE_CALENDAR_ADMIN', 15 => 'ROLE_GALLERY_ADMIN', 19 => 'ROLE_ARTICLE_ADMIN');
$container->setParameter('roleUserRights', $roleUserRightsArr);
$defaultTeamTableSettings = array(
    '1' => array('id' => "$primaryEmail", 'type' => 'CF', 'club_id' => '1', 'name' => "CF_$primaryEmail"),
    '2' => array('id' => '47', 'type' => 'CF', 'club_id' => '1', 'name' => 'CF_47'),
    '3' => array('id' => '79', 'type' => 'CF', 'club_id' => '1', 'name' => 'CF_79'),
    '4' => array('id' => '77', 'type' => 'CF', 'club_id' => '1', 'name' => 'CF_77'),
    '5' => array('id' => '86', 'type' => 'CF', 'club_id' => '1', 'name' => 'CF_86'),
    '6' => array('id' => 'birth_year', 'type' => 'G', 'club_id' => '8573', 'name' => 'Gbirth_year'),
    '7' => array('id' => 'age', 'type' => 'G', 'club_id' => '8573', 'name' => 'Gage')
);
$container->setParameter('default_team_table_settings', $defaultTeamTableSettings);

$defaultInternalDocumentTableSettings = array(
    '1' => array('id' => "SIZE", 'type' => 'FO', 'club_id' => '1', 'name' => "T_FO_SIZE"),
    '2' => array('id' => "LAST_UPDATED", 'type' => 'DO', 'club_id' => '1', 'name' => "T_DO_LAST_UPDATED"),
);
$container->setParameter('default_internal_documents_table_settings', $defaultInternalDocumentTableSettings);
//Editorial/Archive colum settings default columns
$defaultInternalArticleTableSettings = array(
    '0' => array('id' => "PUBLICATION_DATE", 'type' => 'AS', 'club_id' => '1', 'name' => "AR_AS_PUBLICATION_DATE"),
    '1' => array('id' => "ARCHIVING_DATE", 'type' => 'AS', 'club_id' => '1', 'name' => "AR_AS_ARCHIVING_DATE"),
    '2' => array('id' => "AREAS", 'type' => 'AS', 'club_id' => '1', 'name' => "AR_AS_AREAS"),
    '3' => array('id' => "CATEGORIES", 'type' => 'AS', 'club_id' => '1', 'name' => "AR_AS_CATEGORIES"),
    '4' => array('id' => "CREATED_BY", 'type' => 'AE', 'club_id' => '1', 'name' => "AR_AE_CREATED_BY"),
    '5' => array('id' => "EDITED_AT", 'type' => 'AE', 'club_id' => '1', 'name' => "AR_AE_EDITED_ON"),
    '6' => array('id' => "EDITED_BY", 'type' => 'AE', 'club_id' => '1', 'name' => "AR_AE_EDITED_BY"),
);
$container->setParameter('default_internal_article_table_settings', $defaultInternalArticleTableSettings);
/* for handle menu permission of internal area  */
$groupPermissions = array(
    "overview" => array(0 => "ROLE_GROUP_ADMIN", 1 => "MEMBER"),
    "teammember" => array(0 => "MEMBER", 1 => "ROLE_GROUP_ADMIN", 2 => "ROLE_CONTACT_ADMIN"),
    "document" => array(0 => "ROLE_GROUP_ADMIN", 1 => "ROLE_DOCUMENT_ADMIN", 2 => "MEMBER"),
    "forum" => array(0 => "ROLE_GROUP_ADMIN", 1 => "ROLE_FORUM_ADMIN", 2 => "MEMBER")
);
$container->setParameter('groupMenuPermissions', $groupPermissions);
$container->setParameter('defaultLanguages', array('en','de','fr','it'));


//page limit settings
//Notes
$container->setParameter('pagelimit', '10');
$container->setParameter('start_offset', '0');
//Forum
$container->setParameter('forumPostsPerPage', '20');
$container->setParameter('noreplyEmail', 'noreply@fairgate.ch');
$staticBoxClubIds = array(0 => 151, 1 => 205, 2 => 206, 3 => 154, 4 => 207, 5 => 208, 6 => 209, 7 => 210, 8 => 211, 9 => 155);
$container->setParameter('staticBoxClubIds', $staticBoxClubIds);
$container->setParameter('defaultColorClubOrRole', '#428BCA');
$container->setParameter('defaultColorFedLevel', '#1D943B');
// google captcha keys in forgot password page
//Keys for demo.mypits.org
//$container->setParameter('googleCaptchaSitekey', '6Lc9yBMTAAAAAJ7yqLMTRE6F3ana8Cj2CE4gvnKm');
//$container->setParameter('googleCaptchaSecretkey', '6Lc9yBMTAAAAAPvA8jCniJRARucKS_xWgmO_-urg');

//keys for all domains in live server
$container->setParameter('googleCaptchaSitekey', '6LeKIyATAAAAAMZ9FMZMkv8zs2aU-mrGv_X-DePn');
$container->setParameter('googleCaptchaSecretkey', '6LeKIyATAAAAAGhqhRINm_884Jzh82cGLvdUbxdX');

// google captcha keys in forgot password page  ends

//set default timezone for calendar as Europe/Zurich
$container->setParameter('calendarGlobalTimeZone', 'Europe/Zurich');
$container->setParameter('club_total_space', '1000');

$container->setParameter('root_server_avast_phpfile', 'http://5.148.186.139/virus_scan.php');
$container->setParameter('avast_scan', 1);
$container->setParameter('avast_scan_upload_folder', '/home/nfs_share/');

//SET THE CURRENT SPRINT
$container->setParameter('currentSprint', 'release_4_6_4_1');

/* userrights */
$container->setParameter('club_calendar_admin', 14);
$container->setParameter('club_gallery_admin', 16);
$container->setParameter('group_admin', 9);
$container->setParameter('club_admin', 2);
$container->setParameter('fed_admin', 17);
$container->setParameter('club_article_admin', 20);
$container->setParameter('role_article_admin', 19);
$container->setParameter('cms_admin', 18);
$container->setParameter('page_admin', 21);

//lazy loading
$container->setParameter('lazyLoadingPerRequest', '10');

//External Application
$externaltelG = 72585;
$externalEmployer = 73029;
$personalNumber = 72581;
$externalCategory = 1;
$externalApplSenderMail = 'noreply@fairgate.ch';
$externalApplSenderName = 'Fairgate AG';
$externalApplicationSystemFields = array('firstName' => $firstName, 'lastName' => $lastName, 'gender' => $gender, 'dob' => $dob, 'email' => $primaryEmail, 'street' => $correspondaceStrasse, 'location' => $correspondaceOrt, 'zipcode' => $correspondacePlz, 'mobile' => $mobile1, 'telg' => $externaltelG, 'employer' => $externalEmployer, 'personalNumber' => $personalNumber);
$container->setParameter('external_application_system_fields', $externalApplicationSystemFields);
$externalApplicationClubIds = array(157);
$container->setParameter('external_application_clubids', $externalApplicationClubIds);
$container->setParameter('external_application_mail_sender_name', $externalApplSenderName);
$container->setParameter('external_application_sender_email', $externalApplSenderMail);
$container->setParameter('external_application_fedfield_category', $externalCategory);

//API static fields
$apiTelefon = 72371;
$apiMagazin = 72382;
$container->setParameter('api_satus_telefon', $apiTelefon);
$container->setParameter('api_satus_magazin', $apiMagazin);

// Cms article and calendar
$cmsRoleIdClub = 3;
$cmsRoleIdSubFed = 2;
$cmsRoleIdFed = 1;
$container->setParameter('cms_roleid_club', $cmsRoleIdClub);
$container->setParameter('cms_roleid_subfed', $cmsRoleIdSubFed);
$container->setParameter('cms_roleid_fed', $cmsRoleIdFed);
$cmsRoleIdArray = array('federation'=>$cmsRoleIdFed, 'sub-federation'=>$cmsRoleIdSubFed, 'club'=>$cmsRoleIdClub);
$container->setParameter('cms_club_level_roleids', $cmsRoleIdArray);
//Alert mails for stuck newsletters
$notificationMailIds = array('daniel.schweri@fairgate.ch', 'patrick.scheller@fairgate.ch', 'david.herzog@fairgate.ch', 'tobias.nafzger@fairgate.ch', 'ajesh@pitsolutions.com', 'rajasree.p@pitsolutions.com', 'deepa@pitsolutions.com');
$container->setParameter('newsletter_notification_mails', $notificationMailIds);
$companyWithoutMainContactArray = array(9); 
$companyFieldsArray=array(2,23,1,72);
$companyWithMainContactArray =array_merge($companyWithoutMainContactArray,$companyFieldsArray);
$container->setParameter('companyfields', $companyFieldsArray);
$container->setParameter('companywithmaincontactfields', $companyWithMainContactArray);
$container->setParameter('companywithoutmaincontactfields', $companyWithoutMainContactArray);
$apiDomains = array(
 'api.fairgate.ch:13014',    
    
);
$container->setParameter('apiDomains', $apiDomains);
$container->setParameter('fairgateAnalyticKey', 'UA-34074203-41');
$container->setParameter('fairgateAnalyticScreenNameArray', array('contact' => 'contacts-fairgate', 'website' => 'cms-fairgate', 'internal' => 'internal-fairgate', 'document' => 'documents-fairgate', 'communication' => 'communications-fairgate', 'sponsor' => 'sponsors-fairgate', 'invoice' => 'invoices-fairgate', 'backend' => 'backend-fairgate', 'settings' => 'settings-fairgate',
    'filemanager' => 'settings-fairgate'));
