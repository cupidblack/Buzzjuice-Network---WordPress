<?php
function convert_media_links($text, $direction = 'ww_to_wp') {
    if ($direction === 'ww_to_wp') {
        $text = preg_replace('#https?://buzzjuice\.net/streams/upload/#', '/../streams/upload/', $text);
    } else {
        $text = preg_replace('#https?://buzzjuice\.net/wp-content/uploads/#', '/../wp-content/uploads/', $text);
    }
    return $text;
}
?>