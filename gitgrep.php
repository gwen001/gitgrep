<?php

include( 'functions.php' );

// init variables
{
    $config = [
        'tokens' => '',
        'max_exception' => 3,
        'min_result' => 10,
        'max_result_displayed' => 3,
        'github_tokens' => 10,
        'context_lines' => 3,
        'fix_max_length' => 350,
    ];

    $output = [
        'error' => false,
        'message' => '',
        'items' => '',
        'page' => 1,
    ];

    if( !($tokens=getenv('GITHUB_TOKENS')) ) {
        $output['error'] = true;
        $output['message'] = 'missing tokens';
        _exit( $output );
    }

    $config['tokens'] = explode( ',', $tokens );
    $n_exception = 0;
    $n_result = 0;
    $t_results = [];
}
// ...


// handle POST params
{
    if( !isset($_POST['search_filter']) || ($search_filter=trim($_POST['search_filter'])) == '' ) {
        $output['error'] = true;
        $output['message'] = 'missing search param';
        _exit( $output );
    }

    if( !isset($_POST['search_regexp']) || ($search_regexp=trim($_POST['search_regexp'])) == '' ) {
        $output['error'] = true;
        $output['message'] = 'missing regexp param';
        _exit( $output );
    }

    if( !isset($_POST['page']) || ($page=(int)$_POST['page']) <= 1 ) {
        $page = 1;
    }

    $search_filter = urlencode( $search_filter );
    $search_regexp_compiled = '#(.{0,100})(' . $search_regexp . ')(.{0,100})#i';
    $search_regexp_compiled = '~' . $search_regexp . '~i';
}
// ...


// main loop
{
    for( ; ; )
    {
        $t_json = github_search( $config, $search_filter, $page );

        if( !is_array($t_json) || array_key_exists('documentation_url',$t_json) || !array_key_exists('items',$t_json) ) {
            $n_exception += 1;
            if( $n_exception >= $config['max_exception'] ) {
                // too many errors or no result, get out!
                $output['error'] = true;
                $output['message'] = 'api limit exceeded';
                break;
            }
            continue;
        }

        // we didn't get any result
        if( count($t_json['items']) == 0 ) {
            $output['error'] = true;
            $output['message'] = 'no more result';
            break;
        }

        $page += 1;

        // apply the regexp on the search results
        $t_found = search_regexp( $config, $t_json['items'], $search_regexp_compiled );

        if( count($t_found) != 0 ) {
            $t_results = array_merge( $t_results, $t_found );
            $n_result = count( $t_results );
        }

        // we have enough results to display, get out!
        // if( $n_result >= $config['min_result'] ) {
            break;
        // }
    }
}
// ...

$output['page'] = $page;
$output['items'] = $t_results;

_exit( $output );
