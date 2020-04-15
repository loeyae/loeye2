- settings: [master] # Required
  module:
    module_id: <{$property}>.home # Required
    plugin:               # Required
        -
            name: \loeye\plugin\TranslatorPlugin
    view:
        default:
            tpl: <{$property}>.home.tpl