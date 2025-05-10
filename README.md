# 网易云音乐搜索 API

这是一个简单的网易云音乐搜索 API 接口，允许用户通过 HTTP 请求搜索网易云音乐平台上的歌曲。

## 功能特点

- 支持按歌曲名称搜索
- 可自定义返回结果数量
- 跨域支持
- 简单易用的 API 接口

## 使用方法

### 请求参数

| 参数名 | 类型 | 必填 | 说明 |
|-------|------|-----|------|
| name | string | 是 | 歌曲名称 |
| limit | int | 否 | 返回结果数量，默认10，范围1-100 |

### 请求示例

```
GET /index.php?name=海阔天空&limit=5
```

### 响应格式

接口返回 JSON 格式数据，包含搜索结果。

### 错误处理

当请求参数不正确或服务器出现错误时，API 会返回相应的错误信息：

```json
{
  "code": 400,
  "message": "请提供歌曲名称"
}
```

或

```json
{
  "code": 500,
  "message": "错误信息"
}
```

## 技术实现

该 API 使用了以下技术：

- PHP 作为后端语言
- cURL 用于发送 HTTP 请求
- BCMath 用于大数运算
- AES 和 RSA 加密算法用于请求参数加密

## 部署要求

1. PHP 7.0 或更高版本
2. 启用 PHP 的 cURL 扩展
3. 启用 PHP 的 BCMath 扩展
4. 启用 PHP 的 OpenSSL 扩展

## 安装步骤

1. 将 `index.php` 文件上传到您的 Web 服务器
2. 确保 PHP 环境满足上述要求
3. 通过浏览器或其他 HTTP 客户端访问 API

## 安全说明

本项目仅用于学习和研究，请勿用于商业用途。使用本 API 时请遵守网易云音乐的用户协议和相关法律法规。

## 特别说明

本项目灵感来自 [MiChongGET/CloudMusicApi](https://github.com/MiChongGET/CloudMusicApi)，感谢原作者的贡献。

## 许可证

MIT License

## 贡献

欢迎提交 Issues 和 Pull Requests 来完善本项目。
