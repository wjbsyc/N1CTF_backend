# /api/challenges （获取题目信息）

参数: class (分类，可选，没有表示全部)
失败响应: {
"code": 401,
"message": "Please Login!",
"success": false
}
成功响应: {
"code": 200,
"success": true,
"challs": [
{
"id": 1,
"title": "1",
"score": 1000,
"solversCount": 1,
"passed": true
},
{
"id": 2,
"title": "2",
"score": 10000,
"solversCount": 0,
"passed": false
}
]
}
方法: GET
登录: 是