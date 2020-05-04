<?php

function _exit( $output ) {
    header( 'Content-type: application/json' );
    echo json_encode( $output );
    // switch( json_last_error() ) {
    //     case JSON_ERROR_NONE:
    //         echo ' - Aucune erreur';
    //     break;
    //     case JSON_ERROR_DEPTH:
    //         echo ' - Profondeur maximale atteinte';
    //     break;
    //     case JSON_ERROR_STATE_MISMATCH:
    //         echo ' - Inadéquation des modes ou underflow';
    //     break;
    //     case JSON_ERROR_CTRL_CHAR:
    //         echo ' - Erreur lors du contrôle des caractères';
    //     break;
    //     case JSON_ERROR_SYNTAX:
    //         echo ' - Erreur de syntaxe ; JSON malformé';
    //     break;
    //     case JSON_ERROR_UTF8:
    //         echo ' - Caractères UTF-8 malformés, probablement une erreur d\'encodage';
    //     break;
    //     default:
    //         echo ' - Erreur inconnue';
    //     break;
    // }
    exit();
}


// perform the code search on GitHub
function github_search( $config, $search_filter, $page )
{
    $n_tokens = count($config['tokens']) - 1;
    $headers = [ 'Authorization: token '.$config['tokens'][rand(0,$n_tokens)] ];
    $url = 'https://api.github.com/search/code?s=indexed&type=Code&o=desc&q=' . $search_filter . '&page=' . $page;
    // echo 'calling search_code: ' . $url ."\n";

    $c = curl_init();
    curl_setopt( $c, CURLOPT_URL, $url );
    curl_setopt( $c, CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $c, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (X11; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0' );
    curl_setopt( $c, CURLOPT_TIMEOUT, 15 );
    curl_setopt( $c, CURLOPT_FOLLOWLOCATION, false );
    curl_setopt( $c, CURLOPT_HEADER, 0 );
    curl_setopt( $c, CURLOPT_RETURNTRANSFER, 1 );
    $response = curl_exec( $c );
    $infos = curl_getinfo( $c );
    curl_close( $c );

    if( $infos['http_code'] != 200 ) {
        return false;
    }

    return json_decode( $response, true );
}


// 	# apply the regexp filter
function search_regexp( $config, $items, $search_regexp )
{
    $n_items = count( $items );
    $t_curl = [];
    $master = curl_multi_init();

    for( $i=0 ; $i<$n_items ; $i++ )
    {
        $raw_url = get_raw_url( $items[$i] );
        $t_curl[$i] = curl_init( $raw_url );
        curl_setopt( $t_curl[$i], CURLOPT_RETURNTRANSFER, true );
        curl_multi_add_handle( $master, $t_curl[$i] );
    }

    do {
        curl_multi_exec( $master, $running );
    } while( $running > 0 );

    for( $i=0 ; $i<$n_items ; $i++ )
    {
        $code = curl_multi_getcontent( $t_curl[$i] );

        if( strlen($code) )
        {
            $m = preg_match_all( $search_regexp, $code, $matches );
            
            if( $m && is_array($matches) && count($matches) )
            {
                $n_match = count( $matches[0] );
                $matches = reorder_matches( $matches, $config['max_result_displayed'] );
                $t_found[] = create_output_item( $items[$i], $matches, $n_match );
            }
        }
    }

    return $t_found;
}


// get the url of the file containing the raw code
function get_raw_url( &$item )
{
    $raw_url = $item['html_url'];
    $raw_url = str_replace( 'https://github.com/', 'https://raw.githubusercontent.com/', $raw_url );
    $raw_url = str_replace( '/blob/', '/', $raw_url );
    $item['raw_url'] = $raw_url;
    return $raw_url;
}


// reorder output of preg_match to fit our needs
function reorder_matches( $matches, $max_result_displayed )
{
    $reorder = [];
    $n_match = count( $matches[0] );

    for( $i=0; $i<$n_match && $i<$max_result_displayed ; $i++ ) {
        $tmp= [
            htmlentities( utf8_encode($matches[1][$i]) ),
            htmlentities( utf8_encode($matches[2][$i]) ),
            htmlentities( utf8_encode($matches[3][$i]) ),
        ];
        $reorder[] = $tmp;
    }

    return $reorder;
}


// keep only interesting things for the front output
function create_output_item( $item, $matches, $n_match )
{
    $tmp = [
        'file_path' => $item['path'],
        'file_html_url' => $item['repository']['html_url'] . '/blob/master/' . $item['path'],
        'file_raw_url' => $item['raw_url'],
        'repository_full_name' => $item['repository']['full_name'],
        'repository_html_url' => $item['repository']['html_url'],
        'owner_login' => $item['repository']['owner']['login'],
        'owner_html_url' => $item['repository']['owner']['html_url'],
        'owner_avatar_url' => $item['repository']['owner']['avatar_url'],
        'n_match' => $n_match,
        'matches' => $matches,
    ];

    return $tmp;
}