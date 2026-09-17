import urllib.request, urllib.parse, http.cookiejar, re, os

base = os.environ.get('TEST_BASE_URL', 'http://127.0.0.1:18080')
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

def get(path):
    return client.open(base + path).read().decode()

def post(path, data):
    return client.open(base + path, urllib.parse.urlencode(data).encode()).read().decode()

s = get('/login')
token = re.search(r'name="_token" value="([^"]+)"', s).group(1)
s = post('/login', {'_token': token, 'email': 'prof@example.org', 'password': 'senha-de-teste-123'})
assert 'Professor' in s

for path in ['/dashboard', '/alunos', '/oficinas', '/produtos', '/materiais', '/materiais/criar', '/producoes', '/producoes/criar', '/estoque', '/relatorios', '/admin']:
    s = get(path)
    assert 'Não foi possível atender' not in s, path

admin = get('/admin')
assert 'Alunos atendidos' in admin
assert 'Gerenciar alunos' in admin
assert '/alunos/criar' in admin

s = get('/materiais/criar')
assert 'Costura' in s

report = get('/relatorios?period_mode=monthly&year=2026&month=9')
assert 'Indicadores do período' in report
assert 'Situação atual' in report
assert 'Entregas pendentes atualmente' in report
assert 'Produção por oficina' in report
assert 'Produção por aluno' in report
assert 'Materiais no período' in report

for path in ['/materiais/1/editar', '/estoque/3/editar']:
    try:
        get(path)
    except urllib.error.HTTPError as e:
        if path.startswith('/estoque/'):
            assert e.code == 404
        else:
            raise

try:
    post('/materiais', {'name': 'forjado'})
except urllib.error.HTTPError as e:
    assert e.code == 403
else:
    raise AssertionError('CSRF ausente aceito')

assert 'Data;' in get('/relatorios?export=csv')

import base64
jpeg = base64.b64decode(os.environ['TEST_JPEG_BASE64']) if 'TEST_JPEG_BASE64' in os.environ else None
if jpeg is None:
    import subprocess
    jpeg = subprocess.check_output([os.environ.get('TEST_PHP', 'php'), '-r', '$i=imagecreatetruecolor(80,120); imagejpeg($i);'])
s = get('/oficinas/criar')
token = re.search(r'name="_token" value="([^"]+)"', s).group(1)
def upload(payload, filename):
    boundary = 'gestaoTestBoundary'
    body = b''
    for key, value in {'_token': token, 'name': 'Oficina imagem HTTP', 'description': 'Teste de upload'}.items():
        body += (f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n').encode()
    body += (f'--{boundary}\r\nContent-Disposition: form-data; name="image"; filename="{filename}"\r\nContent-Type: image/jpeg\r\n\r\n').encode() + payload + f'\r\n--{boundary}--\r\n'.encode()
    return client.open(urllib.request.Request(base+'/oficinas', data=body, headers={'Content-Type': 'multipart/form-data; boundary='+boundary})).read().decode()
page = upload(jpeg, 'teste.jpg')
assert 'sucesso' in page.lower(), page
paths = re.findall(r'/uploads/workshops/[a-f0-9]{24}\.jpg', get('/oficinas'))
assert paths, 'Imagem não aparece na oficina'
response = client.open(base+paths[-1])
assert response.headers['Content-Type'].startswith('image/jpeg')
thumbnail = response.read()
# Read JPEG SOF dimensions without third-party libraries.
pos = 2
while pos < len(thumbnail):
    marker = thumbnail[pos+1]; length = int.from_bytes(thumbnail[pos+2:pos+4], 'big')
    if marker in (0xc0, 0xc2):
        assert (int.from_bytes(thumbnail[pos+7:pos+9], 'big'), int.from_bytes(thumbnail[pos+5:pos+7], 'big')) == (640,360)
        break
    pos += 2+length
else:
    raise AssertionError('JPEG sem dimensões')
assert 'JPEG/JPG' in upload(b'isto nao e jpeg', 'falso.jpg')

s = get('/dashboard')
token = re.search(r'name="_token" value="([^"]+)"', s).group(1)
post('/logout', {'_token': token})

s = get('/login')
token = re.search(r'name="_token" value="([^"]+)"', s).group(1)
post('/login', {'_token': token, 'email': 'aluno@example.org', 'password': 'senha-de-teste-123'})
for path in ['/admin', '/alunos', '/producoes/criar', '/oficinas', '/produtos', '/relatorios']:
    try:
        get(path)
    except urllib.error.HTTPError as e:
        assert e.code == 403, path
    else:
        raise AssertionError('Perfil legado aluno acessou ' + path)

print('OK: HTTP login/logout, páginas, Admin/Alunos, relatórios, CSV, CSRF e permissões')
