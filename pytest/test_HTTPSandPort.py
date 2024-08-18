import requests

def test_HTTPSandPort():
 url = "https://localhost:443" #expect localhost to be listening on port 443 through HTTPS
 try: 
  response = requests.get(url)
  assert response.status_code == 200
  assert response.url.startswith("https")
  assert response.url.endswith(":443")
 except requests.ConnectionError:
  assert False, "Connection error occurred.Are you sure the server is running on port 443?"