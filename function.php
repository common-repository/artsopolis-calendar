<?php

if (!function_exists('acFixUnclosedTags')) {
    function acFixUnclosedTags ( $html ) {
        #put all opened tags into an array
        preg_match_all ( "#<([a-z]+)( .*)?(?!/)>#iU", $html, $result );
        $openedtags = $result[1];
        #put all closed tags into an array
        preg_match_all ( "#</([a-z]+)>#iU", $html, $result );
        $closedtags = $result[1];
        $len_opened = count ( $openedtags );

        # all tags are closed
        if( count ( $closedtags ) == $len_opened ) {
            return $html;
        }
        $openedtags = array_reverse ( $openedtags );
        # close tags
        for( $i = 0; $i < $len_opened; $i++ ) {
            if ( !in_array ( $openedtags[$i], $closedtags ) ) {
                $html .= "</" . $openedtags[$i] . ">";
            }
            else {
                unset ( $closedtags[array_search ( $openedtags[$i], $closedtags)] );
            }
        }
        return $html;
    }
}

if (! function_exists('acTruncate')) {
    function acTruncate($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }
                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length> $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
                // if the maximum length is reached, get off the loop
                if($total_length>= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
        // add the defined ending to the text
        $truncate .= $ending;
        if($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }
        return $truncate;
    }
}

if (!function_exists('acFormatContent')) {
    function acFormatContent($content) {
        //$content = apply_filters( 'the_excerpt', $content );
        return wpautop(str_replace( ']]>', ']]&gt;', $content ));
    }
}

if (!function_exists('acConvertContentEditorToHtml')) {
     function acConvertContentEditorToHtml($content, $stripslash = false) {

        //$content = apply_filters( 'the_content', $content );
        $content = str_replace( ']]>', ']]&gt;', $content );
        $content = str_replace('\r\n', '', $content);
        return $stripslash ? stripslashes($content) : $content;
    }
}

if (!function_exists('acGetStringByLength')) {

    function acGetStringByLength($str, $max_length, $is_filter = false) {

        $str = acConvertContentEditorToHtml($str);

        if($max_length === null || $max_length === 0) { // get all
            if($is_filter === true) {
                //$str = apply_filters( 'the_content', $str );
                $str =  str_replace( ']]>', ']]&gt;', $str );
            }

            return array(
                'text' => acFixUnclosedTags($str),
                'have_more' => false,
            );
        }

        if(strlen($str) <= $max_length) {
            if($is_filter === true) {
                //$str = apply_filters( 'the_content', $str );
                $str =  str_replace( ']]>', ']]&gt;', $str );
            }

            return array(
                'have_more' => false,
                'text' => acFixUnclosedTags($str),
            );
        }

        $str = acTruncate($str, $max_length);

        if($is_filter === true) {
            //$str = apply_filters( 'the_content', $str . '...' );
            $str =  str_replace( ']]>', ']]&gt;', $str );
        }

        return array(
            'have_more' => true,
            'text' => acFixUnclosedTags($str),

        );
    }
}


function ac_handle_empty_datetime($object, &$returnTime, $step = '') {
    
    if (!$step) $step = time() + 24 * 3600 * 365 * 10;
    $time = $object->eventDatesTimes->datetime;
    
    $timeC = $time && isset($time[0]) && trim((string)$time[0]->date) && trim((string)$time[0]->time) ? 1 : 0;
    
    if (!$timeC) {
        $returnTime += $step;
    }
}

function ac_sort_by_start_date($a, $b) {
    
    $s1 = $s2 = 0;
    if ($a->eventDateBegin) {
        $bg1 = explode('-', (string) $a->eventDateBegin);
        $s1 = mktime(0, 0, 0, $bg1[0], $bg1[1], $bg1[2]);
    }
    
    if ($b->eventDateBegin) {
        $bg2 = explode('-', (string) $b->eventDateBegin);
        $s2 = mktime(0, 0, 0, $bg2[0], $bg2[1], $bg2[2]);
    }
    
    ac_handle_empty_datetime($a, $s1);
    ac_handle_empty_datetime($b, $s2);
    
    if ($s1 == $s2) return 0;
    
    return ($s1 < $s2) ? -1 : 1;
}

function ac_sort_by_start_upcomming_time( $a, $b ) {
    
    $s1 = $s2 = 0;
    
    if ($a->eventDatesTimes->datetime) {
        $s1 = intval($a->eventDatesTimes->datetime[0]->timestamp);
    }
    
    if ($b->eventDatesTimes->datetime) {
        $s2 = intval($b->eventDatesTimes->datetime[0]->timestamp);
    }
    
    
    if ( intval( $s1 ) == intval( $s2 ) ) {
        return ac_sort_by_start_date($a, $b);
    }
        
    // If the datetime of this event is empty, move it to the bottom
    ac_handle_empty_datetime($a, $s1);
    ac_handle_empty_datetime($b, $s2);
    
    if ($s1 == $s2) return 0;
    return ($s1 < $s2) ? -1 : 1;
}

function ac_sort_by_end_date($a, $b) {
    
    $s1 = $s2 = 0;
    
    if ($a->eventDateEnd) {
        $bg1 = explode('-', (string) $a->eventDateEnd);
        $s1 = mktime(0, 0, 0, $bg1[0], $bg1[1], $bg1[2]);
    }
    
    if ($b->eventDateEnd) {
        $bg2 = explode('-', (string) $b->eventDateEnd);
        $s2 = mktime(0, 0, 0, $bg2[0], $bg2[1], $bg2[2]);
    }
    
    // If the datetime of this event is empty, move it to the bottom
    ac_handle_empty_datetime($a, $s1);
    ac_handle_empty_datetime($b, $s2);
    
    if ($s1 == $s2) return 0;
    return ($s1 < $s2) ? -1 : 1;
}

function ac_sort_by_alpha($a, $b) {
    
    // If the datetime of this event is empty, move it to the bottom
    
    $s1 = str_replace(' ', '', (string) $a->eventName);
    if ($time = $a->eventDatesTimes->datetime) {
        $firstLetter = substr($s1, 0, 1);
        $s1 = $time && isset($time[0]) && trim((string)$time[0]->date) && trim((string)$time[0]->time) ? $s1 : 'w '. $firstLetter;
    }
    
    $s2 = str_replace(' ', '', (string) $b->eventName);
    if ($time = $b->eventDatesTimes->datetime) {
        $firstLetter = substr($s2, 0, 1);
        $s2 = $time && isset($time[0]) && trim((string)$time[0]->date) && trim((string)$time[0]->time) ? $s2 : 'w '. $firstLetter;
    }
    
    return strcasecmp($s1 , $s2);
}

function ac_admin_parent_sort_by_alpha($a, $b) {
    return strcasecmp($a['name'] , $b['name']);
}

function ac_admin_sub_sort_by_alpha($a, $b) {   
    return strcasecmp($a , $b);
}

/** @ticket #25103: Git-248 Calendar plugin date display option
 * git: https://git.elidev.info/nhanlt/apollo-theme/-/issues/248
 */
function ac_sort_by_event_active_date ($a, $b) {
    $s1 = $s2 = 0;

    if ($a->activeDate) {
        $s1 = intval($a->activeDate);
    }

    if ($b->activeDate) {
        $s2 = intval($b->activeDate);
    }

    // If the datetime of this event is empty, move it to the bottom
    ac_handle_empty_datetime($a, $s1);
    ac_handle_empty_datetime($b, $s2);

    if ($s1 == $s2) return 0;
    return ($s1 < $s2) ? -1 : 1;
}

if ( ! function_exists( 'ac_get_current_domain' ) ) {
    function ac_get_current_domain() {
        
        if ( is_multisite() ) {
            $site = get_blog_details(get_current_blog_id());
            return $site->domain;
        }
        
        return '';
    }
    
}

// Thienld: checking event expired date by current local time (get from time zone is set in backend > setting > general).
if(! function_exists( 'acIsExpiredTime' )){

    function acDateCompare($a, $b)
    {
        $t1 = strtotime($a->end_time);
        $t2 = strtotime($b->end_time);
        return $t1 - $t2;
    }

    function acIsExpiredTime($event_end_date, $event_date_time = array())
    {
        if(empty($event_date_time) || empty($event_end_date)) return true; // force event expired if have error data
        usort($event_date_time, 'acDateCompare');
        $data = $event_date_time;
        $dateEvent = trim($event_end_date);
        $dateEventArr = explode('-',$dateEvent);
        if(empty($dateEventArr)) return true; // force event expired if have error data
        $dateEvent = $dateEventArr[2] . '-' . $dateEventArr[0] . '-' . $dateEventArr[1];
        $timeTo = empty($data[0]->end_time) ? "23:59" : $data[0]->end_time; // set time_to = 23:59 if had no chosen end time for this event
        $unixTime = strtotime($dateEvent . ' ' . $timeTo);
        $currentTime = current_time('timestamp');
        return $unixTime < $currentTime;
    }



}

if(! function_exists( 'ac_sort_ongoing' )) {
    function ac_sort_ongoing($a, $b)
    {
        return strcasecmp($a->ongoing, $b->ongoing);
    }
}

/**
 * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
 */
if(! function_exists( 'getPathOverrideCss' )) {
    function getPathOverrideCss($id = ''){
        $id = !$id ? 0 : $id;
        return XML_FILE_PATH. '/override-css/' . $id . '_override_css.css';
    }
}

/**
 * @ticket #20274: [CF] Artsopolis Calendar - In the plugin config form, add an 'Override stylesheet' field - Item 3
 */
if(! function_exists( 'getUrlOverrideCss' )) {
    function getUrlOverrideCss($id){
        $id = !$id ? 0 : $id;
        return WP_CONTENT_URL . '/uploads/' . XML_BASE_NAME. '/' . ac_get_current_domain() . '/override-css/' . $id . '_override_css.css';
    }
}

if(! function_exists( 'ac_debug' )) {
    function ac_debug($data, $isDie) {
        if (isset($_GET['is_debug'])) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';

            if ($isDie) {
                die;
            }
        }
    }
}