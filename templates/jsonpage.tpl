{ "canonbase": "{$canonbase}",
  "service": {$service|@json_encode:64 nofilter},
  "datename": "{$datename}",
  "date": "{$date}",
  "pagename": "{$pagename}",
  "frame": {$frame|@json_encode:64 nofilter},
  "tf": {$tf|@json_encode:64 nofilter},
  "alternatives": {$alternatives|@json_encode:64 nofilter},
  "subpages": {$subpages|@json_encode:64 nofilter},
  "meta": {$meta|@json_encode:64 nofilter}
}