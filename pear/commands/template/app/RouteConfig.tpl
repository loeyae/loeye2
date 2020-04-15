- settings: [master]
  routes:
    home:
        path: ^/$
        module_id: <{$property}>.home

    <{$property}>:
        path : ^/<{$property}>/{module}/$
        module_id : <{$property}>.{module}
        regex:
            module: \w+