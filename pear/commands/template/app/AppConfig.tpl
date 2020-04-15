- settings: [master]
  profile: ${LOEYAE_ACTIVE_PROFILE:local}
  constants:
        BASE_SERVER_URL: http://localhost.com/
  application:
    cache: pfile # One of "apc"; "array"; "file"; "memcached"; "parray"; "pfile"; "redis"
    database:
        default: default
        is_dev_mode: true
        encrypt_mode: explicit # One of "explicit"; "crypt"; "keydb"
  configuration:
    property_name: <{$property}> # Required
    timezone: Asia/Shanghai # Required
  locale:
    default: zh_CN
    basename: lang # Required
    supported_languages: ["zh_CN"]
