/*
    Sends a post request through AJAX, using JQuery under the hood.

    When the sever succeeds, it must send a json-serialized object with the property "success" set to true.
    It must also kill the request, or the server will append a 0 to the end, and make the JSON invalid.

    ajax_url - The url of admin-ajax.php.
    _ajax_nonce - A variable used to validate requests in Wordpress.
    action - Wordpress action which this request will trigger (its actual name is "wp_ajax_$action").
    post_data - The data.
    status_element - A div, p, span, or whatever where we can write messages.
    on_success(resp) - A callback for successful requests. Receives the parsed response object.
    on_error(msg) - A callback for failed requests. Receives an error message.
*/
function THEELEGA_common_post(ajax_url, _ajax_nonce, action, post_data, status_element, on_success, on_error)
{
    var $ = jQuery;
    on_success = on_success ? on_success : function(){};
    on_error = on_error ? on_error : function(){};
    status_element = $(status_element);

    status_element.text('Please wait...');

    var request = 
    {
        _ajax_nonce: _ajax_nonce,
        action: action,
        //Post_data is stringified to circumvent PHP's max_input_vars.
        post_data: JSON.stringify(post_data)
    }

    $.post(ajax_url, request).done(function onPostSuccess(response)
    {
        if (!(response.trim()))
        {
            status_element.text('Server sent no response.');
            on_error('Server sent no response.');
            return;
        }

        response = response.trim();
        var resp_obj = response;

        if (response.startsWith('{'))
        {
            resp_obj = JSON.parse(response);
        }

        if (resp_obj.success)
        {
            status_element.text('Success');
            on_success(resp_obj);
        }
        else
        {
            status_element.text('Error: ' + response);
            on_error(resp_obj);
        }
    })
    .error(function onPostError(xhr, status, error)
    {
        status_element.text('Error: ' + error);
        on_error('Error: '  + error);
    });
}