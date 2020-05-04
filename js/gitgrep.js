$(document).ready(function(){
    $('#btn-search').click(function(){
        init_search();
    });
    // $('#btn-more').click(function(){
    //     search();
    // });
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
        $('#search-spinner').addClass('d-none');
        if( datas['error'] ) {
            $('#result-error').html( datas['message'] );
            $('#result-error').removeClass('d-none');
            $('#search-form').find('#page').val( 1 );
        } else if( datas['items'].length ) {
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
        $('#result-error').html( 'Something went wrong!' );
        $('#result-error').removeClass('d-none');
    });
}

function generate_code( matches )
{
    code = '';
    n_match = matches.length - 1;
    $.each(matches,function(index) {
        code = code + matches[index][0] + '<span class="code-highlight">' + matches[index][1] + '</span>' + matches[index][2];
        if( index < n_match ) {
            code = code + '<br>---<br>';
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
            clone.find('.code').html( generate_code(item['matches']) );
            clone.find('.n-match').html( item['n_match']+' match(es)' );
            clone.removeClass('d-none');
            clone.appendTo( '#results-container' );
        }
    });
}
