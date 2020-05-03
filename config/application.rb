require_relative 'boot'

# require 'rails/all'
require 'active_record/railtie'
# require 'active_storage/engine'
# require 'action_controller/railtie'
# require 'action_view/railtie'
require 'action_mailer/railtie'
require 'active_job/railtie'
require 'action_cable/engine'
# require 'action_mailbox/engine'
# require 'action_text/engine'
# require 'rails/test_unit/railtie'
# require 'sprockets/railtie'

# Require the gems listed in Gemfile, including any gems
# you've limited to :test, :development, or :production.
Bundler.require(*Rails.groups)

module Gitgrep
  class Application < Rails::Application
    # Initialize configuration defaults for originally generated Rails version.
    config.load_defaults 6.0
	# Route exceptions to the application router vs. default
	config.exceptions_app = self.routes
	
	THREAD_POOL_SIZE = 30
    SEARCH_MIN_RESULT = 10
	GITHUB_MAX_EXCEPTION = 3
    GITHUB_TOKENS = ENV['GITHUB_TOKENS'].split(',')
    
    # Settings in config/environments/* take precedence over those specified here.
    # Application configuration can go into files in config/initializers
    # -- all .rb files in that directory are automatically loaded after loading
    # the framework and any gems in your application.
  end
end
