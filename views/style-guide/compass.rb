# http_path = "/"
project_path = "."
css_dir = "assets/css"
sass_dir = "assets/scss"
images_dir = "assets/images"
javascripts_dir = "assets/javascript"
relative_assets = true

environment = :development

output_style = (environment == :production) ? :compressed : :expanded
sourcemap = (environment == :production) ? false : true
