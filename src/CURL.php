<?php
/**
 * Created by IntelliJ IDEA.
 * User: rek
 * Date: 2017/4/11
 * Time: PM3:29
 */

namespace x2ts\curl;


use x2ts\Component;
use x2ts\ComponentFactory as X;

class CURL extends Component {
    /**
     * @param string       $url
     * @param string|array $body
     * @param array        $headers
     *
     * @return array|bool
     */
    public function post(string $url, $body, array $headers = []) {
        $c = $this->curlInit($url, $headers);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);

        $r = curl_exec($c);
        curl_close($c);
        if (false === $r) {
            return false;
        }
        X::logger()->trace($r);

        return $this->parseHttpResponse($r);
    }

    public function put(string $url, $body, array $headers = []) {
        $c = $this->curlInit($url, $headers);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);
        $r = curl_exec($c);
        curl_close($c);
        if (false === $r) {
            return false;
        }
        X::logger()->trace($r);

        return $this->parseHttpResponse($r);
    }

    public function get(string $url, array $headers = []) {
        $c = $this->curlInit($url, $headers);

        $r = curl_exec($c);
        curl_close($c);
        if (false === $r) {
            return false;
        }

        return $this->parseHttpResponse($r);
    }

    /**
     * @param  string $r
     *
     * @return array
     */
    private function parseHttpResponse(string $r): array {
        list($header, $body) = explode("\r\n\r\n", $r, 2);
        if (strpos($header, '100 Continue') !== false) {
            list($header, $body) = explode("\r\n\r\n", $body, 2);
        }
        $headerList = explode("\r\n", $header);
        $statusLine = array_shift($headerList);
        $statusCode = (int) explode(' ', $statusLine)[1];
        $headers = [];
        foreach ($headerList as $header) {
            list($key, $value) = explode(':', $header, 2);
            $headers[$key] = $value;
        }
        return [
            'status'  => $statusCode,
            'headers' => $headers,
            'body'    => $body,
        ];
    }

    /**
     * @param string $url
     * @param array  $headers
     *
     * @return resource
     */
    private function curlInit(string $url, array $headers) {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HEADER, true);
        if (count($headers) > 0) {
            $headerList = [];
            foreach ($headers as $name => $value) {
                $headerList[] = "$name: $value";
            }
            curl_setopt($c, CURLOPT_HTTPHEADER, $headerList);
            return $c;
        }
        return $c;
    }
}