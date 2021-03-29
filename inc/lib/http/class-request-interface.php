<?php

/**
 * Representation of an outgoing, client-side request.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - HTTP method
 * - URI
 * - Headers
 * - Message body
 *
 * During construction, implementations MUST attempt to set the Host header from
 * a provided URI if no Host header is provided.
 *
 * Requests are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 */
interface RequestInterface extends MessageInterface
{
  /**
   * Retrieves the HTTP method of the request.
   *
   * @return string Returns the request method.
   */
  public function getMethod();

  /**
   * Retrieves the URI instance.
   *
   * This method MUST return a UriInterface instance.
   *
   * @see http://tools.ietf.org/html/rfc3986#section-4.3
   * @return UriInterface Returns a UriInterface instance
   *     representing the URI of the request.
   */
  public function getUri();
}
