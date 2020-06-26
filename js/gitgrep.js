$(document).ready(function(){
    if( $(window).width() < 1400 ) {
        $('#site-infos').hide();
    }
    $('#search-filter').focus();
    $('#search-form').submit(function(e){
        e.preventDefault();
        init_search();
    });
});

function init_search()
{
    $('#results-container').html( '' );
    $('#search-form').find('#page').val( 1 );
    search();
}

function search()
{
    // $('#btn-more').addClass('d-none');
    $('#result-error').addClass('d-none');
    $('#search-spinner').removeClass('d-none');

    var request = $.ajax({
        url: '/',
        method: 'POST',
        data: $('#search-form').serialize(),
        dataType: 'json'
    });

    request.done(function( datas ) {
        if( datas['error'] ) {
            $('#search-spinner').addClass('d-none');
            $('#result-error').html( datas['message'] );
            $('#result-error').removeClass('d-none');
            $('#search-form').find('#page').val( 1 );
        } else {
            // if( datas['items'].length ) {
                // $('#search-spinner').addClass('d-none');
            // }
            // $('#btn-more').removeClass('d-none');
            $('#search-form').find('#page').val( datas['page'] );
            window.setTimeout( search, 1000 );
        }
        if( datas['items'].length ) {
            display_results( datas );
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        $('#search-spinner').addClass('d-none');
        $('#result-error').html( 'something went wrong' );
        $('#result-error').removeClass('d-none');
    });
}

function generate_code( matches )
{
    var code = '';
    var n_match = matches.length - 1;

    $.each(matches,function(index) {
        code = code + matches[index][0] + '<span class="code-highlight">' + matches[index][1] + '</span>' + matches[index][2];
        if( index < n_match ) {
            code = code + '<br>---<br>';
        }
    });

    return code;
}

function generate_code_with_lines( matches )
{
    var code = '';
    var n_match = matches.length - 1;

    $.each(matches,function(index) {

        var start_line = matches[index][0];

        for( var i=1 ; i<matches[index].length ; i++ ) {
            code = code + '<div class="line-result">';
            code = code + '<div class="line-number">' + (start_line+i-1) + '</div>';
            code = code + '<div class="line-code">' + matches[index][i] + '</div>';
            code = code + '</div>';
        }

        if( index < n_match ) {
            code = code + '<div class="line-result">';
            code = code + '<div class="line-number">&nbsp;</div>';
            code = code + '<div class="line-code">---</div>';
            code = code + '</div>';
        }
    });

    return code;
}

function display_results( datas )
{
    $.each(datas['items'],function(index) {
        item = datas['items'][index];
        if( item['matches'] && item['matches'].length )
        {
            var clone = $('#result-template').clone( true );
            clone.find('.link-owner').attr( 'href', item['owner_html_url'] );
            clone.find('.owner-avatar').attr( 'src', item['owner_avatar_url'] );
            clone.find('.link-repo').attr( 'href', item['repository_html_url'] );
            clone.find('.link-repo').html( item['repository_full_name'] );
            clone.find('.link-file').attr( 'href', item['file_html_url'] );
            clone.find('.link-file').html( item['file_path'] );
            clone.find('.link-rawfile').attr( 'href', item['file_raw_url'] );
            clone.find('.code').html( generate_code_with_lines(item['matches']) );
            var nmatch = item['n_match']+' match';
            if( item['n_match'] > 1 ) {
                nmatch = nmatch + 'es';
            }
            clone.find('.n-match').html( nmatch );
            clone.removeClass('d-none');
            clone.appendTo( '#results-container' );
        }
    });
}
