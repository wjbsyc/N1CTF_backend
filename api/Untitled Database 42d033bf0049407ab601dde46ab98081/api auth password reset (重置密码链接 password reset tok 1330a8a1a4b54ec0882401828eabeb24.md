# /api/auth/password/reset (重置密码链接 : /password/reset?token=TOKEN&email=EMAIL

参数: 
email , password , 
password_confirmation ,
token

失败响应: {
"code": 400,
"message": "xxxxxxxxxxxxxxxxxxx",
"success": false
}
成功响应: {
"code": 200,
"success": true,
"message": "Your password has been reset!"
}
方法: POST
登录: 否