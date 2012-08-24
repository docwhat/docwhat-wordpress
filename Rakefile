namespace :css do
  desc "compile css"
  task :compile do
    puts "*** compiling css ***"
    FileList["**/config.rb"].each do |filename|
      Dir.chdir File.dirname(filename) do
        system "compass compile" or raise "Unable to run compass"
      end
    end
  end
end
task :css => [:'css:compile']

namespace :js do
  desc "compile javascript"
  task :compile do
    puts "*** compiling javascript ***"
    FileList["**/*.coffee"].each do |filename|
      system "coffee -c #{filename}" or raise "Unable to run coffee"
    end
  end
end
task :js => [:'js:compile']

task :default => [:css, :js]

desc "Deploys the latest code committed in git"
task :deploy do
  puts "*** Deploying the site ***"
  system("ssh gerf.org 'cd Sites/docwhat-wordpress && git pull'") or raise "Unable to deploy code"
end
