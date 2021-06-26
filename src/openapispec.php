<?php

/**
 * @OA\Get(
 *   path="/api/endpoint",
 *   @OA\Response(response=200, description="Success")
 * )
 */
  
/**
 * @OA\Server(
 *      url="{schema}://localhost/backend",
 *      description="OpenApi parameters",
 *      @OA\ServerVariable(
 *          serverVariable="schema",
 *          enum={"https", "http"},
 *          default="https"
 *      )
 * )
 */