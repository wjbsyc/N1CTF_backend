# /api/newchallenge

参数: {
"class":"pwn",
"description" :"1",
"url": "1",
"flag" : "12345",
"info": one of ["start","hide","over"]
"score" : 10000
}
失败响应: {
"code": 400,
"success": false,
"message": "Permission Denied"
}
成功响应: {
"code": 200,
"success": true,
"message": "OK"
}
方法: POST
登录: 管理员