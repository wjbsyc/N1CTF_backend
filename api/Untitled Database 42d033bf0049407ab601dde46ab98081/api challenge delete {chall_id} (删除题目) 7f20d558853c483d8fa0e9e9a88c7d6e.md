# /api/challenge/delete/{chall_id} (删除题目)

参数: 无
失败响应: {
"code": 400,
"success": false,
"message": "Administrator permission is required"
}
成功响应: {
"code": 200,
"success": true,
"message": "OK"
}
方法: DELETE
登录: 管理员