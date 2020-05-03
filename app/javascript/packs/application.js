// This file is automatically compiled by Webpack, along with any other files
// present in this directory. You're encouraged to place your actual application logic in
// a relevant structure within app/javascript and only use these pack files to reference
// that code so it'll be compiled.

// require("@rails/ujs").start()
// require("turbolinks").start()
// require("@rails/activestorage").start()
// require("channels")
require('jquery')

// Uncomment to copy all static images under ../images to the output folder and reference
// them with the image_pack_tag helper in views (e.g <%= image_pack_tag 'rails.png' %>)
// or the `imagePath` JavaScript helper below.
//
// const images = require.context('../images', true)
// const imagePath = (name) => images(name, true)

$(document).ready(function(){
    $('#btn-search').click(function(){
        init_search();
    });
    $('#btn-more').click(function(){
        search();
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
    $('#btn-more').addClass('d-none');
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
            $('#btn-more').removeClass('d-none');
            $('#search-form').find('#page').val( datas['page'] );
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

function generate_code( match )
{
    code = '';
    $.each(match,function(index) {
        code = code + match[index][0] + '<span class="code-highlight">' + match[index][1] + '</span>' + match[index][2] + '<br>';
    });
    return code;
}

function display_results( datas )
{
    $.each(datas['items'],function(index) {
        item = datas['items'][index];
        if( item['match'] && item['match'].length )
        {
            var clone = $('#result-template').clone( true );
            clone.find('.link-owner').attr( 'href', item['owner_html_url'] );
            clone.find('.owner-avatar').attr( 'src', item['owner_avatar_url'] );
            clone.find('.link-repo').attr( 'href', item['repository_html_url'] );
            clone.find('.link-repo').html( item['repository_full_name'] );
            clone.find('.link-file').attr( 'href', item['file_html_url'] );
            clone.find('.link-file').html( item['file_path'] );
            clone.find('.link-rawfile').attr( 'href', item['file_raw_url'] );
            clone.find('.code').html( generate_code(item['match']) );
            clone.removeClass('d-none');
            clone.appendTo( '#results-container' );
        }
    });
}
