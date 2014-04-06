require 'coffee-script'
require 'uglifier'

COFFEE_SRC = FileList['**/*.coffee']

JS_TARGET = COFFEE_SRC.ext('.js')

namespace :css do
  desc 'compile css'
  task :compile do
    puts '*** compiling css ***'
    FileList['**/config.rb'].each do |filename|
      Dir.chdir File.dirname(filename) do
        fail 'Unable to run compass' unless system 'compass compile'
      end
    end
  end
end
task css: [:'css:compile']

namespace :js do
  desc 'compile javascript'
  task :compile do
    puts '*** compiling javascript ***'
    uglifier = Uglifier.new
    COFFEE_SRC.zip(JS_TARGET).each do |src, dst|
      puts "compiling #{dst}"

      js = CoffeeScript.compile File.read(src)
      ugly_js = uglifier.compile(js)
      File.write(dst, ugly_js)
    end
  end
end
task js: [:'js:compile']

task default: [:css, :js]

desc 'Deploys the latest code committed in git'
task :deploy do
  puts '*** Deploying the site ***'
  unless system("ssh gerf.org 'cd Sites/docwhat-wordpress && git pull'")
    fail 'Unable to deploy code'
  end
end
