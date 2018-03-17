{ {if $oembed}
  "type" : "photo",
  "version" : "1.0",
{/if}
  "title" : "{$service.service_name} - Page {$page} from {$date|date_format:"%b %e, %Y"}",
  "thumbnail_url" : "{$canonurl}_120x101.img",
  "thumbnail_width" : "120",
  "thumbnal_height" : "101",
  "url" : "{$canonurl}_{$width}x{$height}.img",
  "width" : "{$width}",
  "height" : "{$height}",

  "canonbase": "{$canonbase}",
  "service": {$service|@json_encode:64 nofilter},
  "datename": "{$datename}",
  "date": "{$date}",
  "pagename": "{$pagename}",
  "frame": {$frame|@json_encode:64 nofilter},
  "tf": "{$tf}",
  "meta": {$meta|@json_encode:64 nofilter}
}