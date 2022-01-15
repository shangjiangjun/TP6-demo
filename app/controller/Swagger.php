<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use \OpenApi;

class Swagger extends BaseController
{
    /**
     * @OA\Swagger(
     *     schemes={"http","https"},
     *     host="demo.tp6.com",
     *     basePath="/swagger",
     *     @OA\Info(
     *         version="1.0.0",
     *         title="接口文档",
     *         description="接口描述",
     *         termsOfService="",
     *         @OA\License(
     *             name="demo.tp6.com",
     *             url="http://demo.tp6.com"
     *         )
     *     ),
     *    @OA\SecurityScheme(
     *     type="http",
     *     in="header",
     *     name="Authorization",
     *     scheme="bearer",
     *     securityScheme="bearerAuth",
     *     bearerFormat="JWT"
     *     )
     * ),
     * @OA\PathItem(
     *     path= "/swagger",
     * )
     */
    public function index()
    {
        // @unlink('./swagger.json');
        // 扫描应用目录
        $path ='../app';
        $swagger = OpenApi\scan($path); // \OpenApi\scan($path);
        header('Content-Type: application/x-yaml');
        $swagger_json_path = './swagger.json';
        // 在public目录下生成swagger.json
        $res = file_put_contents($swagger_json_path,$swagger->toJson());
        if ($res == true) {
            return redirect('/dist/index.html');
        }
    }
}
