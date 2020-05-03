require 'net/http'
require 'json'
require 'to_regexp'

class PagesController < ApplicationController

	skip_before_action :verify_authenticity_token

	def home
	end

	def search
		output = { 'error':false, 'message':'', 'items':'', 'page':1 }

		# check param search_filter
		if !params.has_key?('search_filter') || params[:search_filter].strip.length == 0
			output['error'] = true
			output['message'] = 'missing search param'
			render( {:json =>output} )
			return false
		end

		# check param search_regexp
		if !params.has_key?('search_regexp') || params[:search_regexp].strip.length == 0
			output['error'] = true
			output['message'] = 'missing regexp param'
			render( {:json =>output} )
			return false
		end

		# check param page
		if params.has_key?('page') && params[:page].to_i>=1
			page = params[:page].to_i
		else
			page = 1
		end

		search_filter = params[:search_filter].strip
		search_regexp = '/(.{0,100})(' + params[:search_regexp].strip + ')(.{0,100})/i'
		search_regexp_compiled = search_regexp.to_regexp(detect: true)
		# search_regexp = params[:search_regexp].strip.to_regexp(detect: true)

		# init some variables
		exception = 0
		max_exception = Gitgrep::Application::GITHUB_MAX_EXCEPTION
		n_results = 0
		min_result = Gitgrep::Application::SEARCH_MIN_RESULT
		t_results = []

		# main loop
		while true do

			t_json = github_search( search_filter, page )

			if t_json == false || t_json.has_key?('documentation_url') || !t_json.has_key?('items')
				exception += 1
				if exception >= max_exception
					# too many errors or no result, get out!
					output['error'] = true
					output['message'] = 'api limits exceeded'
					break
				end
				next
			end

			if t_json['items'].length == 0
				output['error'] = true
				output['message'] = 'no more result'
				break
			end

			page += 1
			t_found = search_regexp( t_json['items'], search_regexp_compiled )

			if t_found.length != 0
				n_results = n_results + t_found.length
				t_results.concat( t_found )
			end

			# we have enough results to display, get out!
			if n_results >= min_result
				break
			end
		end

		output['page'] = page
		output['items'] = t_results

		render( {:json =>output} )
	end


	# get the url of the file containing the raw code
	def get_raw_url( item )

		raw_url = item['html_url'];
		raw_url = raw_url.sub! 'https://github.com/', 'https://raw.githubusercontent.com/'
		raw_url = raw_url.sub! '/blob/', '/'
		item['raw_url'] = raw_url
		return raw_url

	end


	# retrieve the raw code
	def get_code( raw_url )

		url = URI.parse( raw_url )
		http = Net::HTTP.new( 'raw.githubusercontent.com', 443 )
		http.use_ssl = true
		# http.verify_mode = OpenSSL::SSL::VERIFY_NONE
		request = Net::HTTP::Get.new( url.to_s )
		puts( "calling raw_url: " + raw_url )
		response = http.request( request )

		if response.code.to_i == 200
			return response.body
		else
			return false
		end
	end


	# keep only interesting things for the front output
	def create_output_item( item, match )
		tmp = {
			'file_path': item['path'],
			'file_html_url': item['repository']['html_url'] + '/blob/master/' + item['path'],
			'file_raw_url': item['raw_url'],
			'repository_full_name': item['repository']['full_name'],
			'repository_html_url': item['repository']['html_url'],
			'owner_login': item['repository']['owner']['login'],
			'owner_html_url': item['repository']['owner']['html_url'],
			'owner_avatar_url': item['repository']['owner']['avatar_url'],
			'match': match
		}
		return tmp
	end


	def grab_and_filter( item, search_regexp )

		raw_url = get_raw_url( item )
		code = get_code( raw_url )
		
		if code == false
			return false
		end

		match = code.scan( search_regexp )
		return match
	end
	

	# apply the regexp filter
	def search_regexp( items, search_regexp )

		t_found = []
		pool_size = Gitgrep::Application::THREAD_POOL_SIZE

		jobs = Queue.new
	
		items.each do |item|
			jobs.push( item )
		end
	
		workers = (pool_size).times.map do
			Thread.new do
				begin
					while x = jobs.pop(true)
						match = grab_and_filter( x, search_regexp )
						if match.length != 0
							t_found.push( create_output_item(x,match) )
						end
					end
					rescue ThreadError
				end
			end
		end
	
		workers.map(&:join)

		return t_found
	end


	# perform the code search on GitHub
	def github_search( search_filter, page )

		t_tokens = Gitgrep::Application::GITHUB_TOKENS
		n_tokens = t_tokens.length - 1

		http = Net::HTTP.new( 'api.github.com', 443 )
		http.use_ssl = true
		# http.verify_mode = OpenSSL::SSL::VERIFY_NONE
		header = { 'Authorization': 'token '+t_tokens[rand(0..n_tokens)] }
		url = '/search/code?s=indexed&type=Code&o=desc&q=' + URI::encode(search_filter) + '&page=' + page.to_s
		puts( "calling search_code: " + url )
		request = Net::HTTP::Get.new( url, header )
		response = http.request( request )

		if response.code.to_i != 200
			return false
		end

		t_json = JSON.parse( response.body )

		return t_json
	end
end
