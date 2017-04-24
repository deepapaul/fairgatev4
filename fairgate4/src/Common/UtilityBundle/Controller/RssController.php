<?php
namespace Common\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class RssController extends Controller
{
    /**
     * Function to get news Feed
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */

    public function getFeedAction() {
        //get the feed url and blog url from setings.yml
        $feed_url = $this->container->getParameter('fgV4_news_rss_feed_url');
        $blog_url = $this->container->getParameter('fgV4_blog_url');
        $content = file_get_contents($feed_url);
        $x = new \SimpleXmlElement($content);
        //get the feed data info
        $feed = $x->channel->item;
        $result = array();
        $rssCount=0;

       //requirement: latest 2 feed display
        foreach($feed as $entry) {
            if($rssCount < 2){
                $subArray = array();
                $date = date('Y-m-d H:i:s', strtotime($entry->pubDate));
                $subArray['date'] = $this->container->get('club')->formatDate($date,'date');
                $subArray['link'] = (string)$entry->link;
                $subArray['title'] = (string)$entry->title;
                $subArray['blog_url'] = $blog_url;
                $subArray['guid'] = (string)$entry->guid;
                $result[] = $subArray;
            }
            $rssCount = $rssCount+1;
        }

        return new JsonResponse($result);

    }//end getFeed()

}//end class Rss