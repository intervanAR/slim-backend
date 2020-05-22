<?php

namespace Backend\Controllers;

class EmpresaMiddleware
{

        /**
     * PSR-3 compliant logger.
     */
    private $logger;

    private $options = [];

    /**
     * Identificador de empresa del requerimiento
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {

        if( "OPTIONS"===$request->getMethod() ){
            $response = $next($request, $response);
            return $response;
        }

        if(isset($this->options ) && isset( $this->options["ignore"] )){
            $uri = "/" . $request->getUri()->getPath();
            $uri = preg_replace("#/+#", "/", $uri);

            /* If request path is matches ignore should not authenticate. */
            foreach ($this->options["ignore"] as $ignore) {
                $ignore = rtrim($ignore, "/");
                if (preg_match("@^{$ignore}(/.*)?$@", $uri)) {
                    $response = $next($request, $response);
                    return $response;
                }
            }
        }

        $hash_key_id = $request->getHeader("Hash-Key-Id") ; 
        
        if( isset($hash_key_id) ) {
            $id_empresa = \Backend\SlimBackend::getEmpresaByHash( $hash_key_id );

            if($id_empresa>0){
                $newRequest = $request->withAttribute('id_empresa', $id_empresa );
                $response = $next($newRequest, $response);
            }else{
                $response = $next($request, $response);
                return $response;
            }
        }else{
                $response = $next($request, $response);
                return $response;
        }
        return $response;
    }

    public function __construct(array $p_options = [] )
    {

        /* Store passed in options overwriting any defaults. */
        $this->options = $p_options;

    }

}