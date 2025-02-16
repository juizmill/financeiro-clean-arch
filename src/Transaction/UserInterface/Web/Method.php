<?php

declare(strict_types=1);

namespace App\Transaction\UserInterface\Web;

enum Method: string
{
    /*
     * Represents an HTTP GET request.
     *
     * Used to retrieve data from a server at the specified resource.
     */
    case GET = 'GET';

    /*
     * Represents an HTTP POST request.
     *
     * Used to submit data to be processed to a specified resource.
     */
    case POST = 'POST';

    /*
     * Represents an HTTP PUT request.
     *
     * Used to update or replace a resource at a specified URI.
     */
    case PUT = 'PUT';

    /*
     * Represents an HTTP DELETE request.
     *
     * Used to delete a resource at a specified URI.
     */
    case DELETE = 'DELETE';

    /*
     * Represents an HTTP PATCH request.
     *
     * Used to apply partial modifications to a resource.
     */
    case PATCH = 'PATCH';

    /*
     * Represents an HTTP HEAD request.
     *
     * Used to retrieve the headers of a resource, identical to a GET request but without the response body.
     */
    case HEAD = 'HEAD';

    /*
     * Represents an HTTP OPTIONS request.
     *
     * Used to describe the communication options for the target resource.
     */
    case OPTIONS = 'OPTIONS';

    /*
     * Represents an HTTP CONNECT request.
     *
     * Used to establish a tunnel to the server identified by a given URI.
     */
    case CONNECT = 'CONNECT';

    /*
     * Represents an HTTP TRACE request.
     *
     * Used to perform a message loop-back test along the path to the target resource.
     */
    case TRACE = 'TRACE';
}
