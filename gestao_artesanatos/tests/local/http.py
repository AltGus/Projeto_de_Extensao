import urllib.request,urllib.parse,http.cookiejar,re
base='http://127.0.0.1:18080'
jar=http.cookiejar.CookieJar();client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
def get(path):return client.open(base+path).read().decode()
def post(path,data):return client.open(base+path,urllib.parse.urlencode(data).encode()).read().decode()
s=get('/login');token=re.search(r'name="_token" value="([^"]+)"',s).group(1)
s=post('/login',{'_token':token,'email':'prof@example.org','password':'senha-de-teste-123'})
assert 'Professor' in s
for path in ['/dashboard','/oficinas','/produtos','/materiais','/materiais/criar','/producoes','/estoque','/relatorios','/admin']:
 s=get(path);assert 'Não foi possível atender' not in s,path
s=get('/materiais/criar');assert 'Costura' in s
for path in ['/materiais/1/editar','/estoque/3/editar']:
 try:get(path)
 except urllib.error.HTTPError as e:
  if path.startswith('/estoque/'): assert e.code==404
  else:raise
try:post('/materiais',{'name':'forjado'})
except urllib.error.HTTPError as e: assert e.code==403
else:raise AssertionError('CSRF ausente aceito')
assert 'Data;' in get('/relatorios?export=csv')
s=get('/dashboard');token=re.search(r'name="_token" value="([^"]+)"',s).group(1);post('/logout',{'_token':token})
s=get('/login');token=re.search(r'name="_token" value="([^"]+)"',s).group(1);post('/login',{'_token':token,'email':'aluno@example.org','password':'senha-de-teste-123'})
try:get('/admin')
except urllib.error.HTTPError as e:assert e.code==403
else:raise AssertionError('Aluno acessou admin')
print('OK: HTTP login, páginas, materiais/oficinas, CSV, CSRF e permissão de aluno')
