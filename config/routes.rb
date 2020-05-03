Rails.application.routes.draw do
  # For details on the DSL available within this file, see https://guides.rubyonrails.org/routing.html
  get '/', to: 'pages#home'
  get '/401', to: 'pages#home'
  get '/403', to: 'pages#home'
  get '/404', to: 'pages#home'
  get '/422', to: 'pages#home'
  get '/500', to: 'pages#home'
  post '/', to: 'pages#search'
end
