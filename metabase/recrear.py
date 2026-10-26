"""Python 3.10+, sin paquetes adicionales. Importa estudiantes.sql antes de ejecutar."""
import pathlib,json,getpass,urllib.request,urllib.error,secrets,argparse
p=argparse.ArgumentParser();p.add_argument('--url',default='http://localhost:3000');p.add_argument('--database-id',type=int,required=True);args=p.parse_args()
root=pathlib.Path(__file__).resolve().parents[1]
key=getpass.getpass('Clave API de Metabase (no se guarda): ')
def api(method,path,body=None):
    req=urllib.request.Request(args.url.rstrip('/')+'/api'+path,data=json.dumps(body).encode() if body is not None else None,headers={'x-api-key':key,'Content-Type':'application/json'},method=method)
    try:
        with urllib.request.urlopen(req,timeout=120) as r: data=r.read()
    except urllib.error.HTTPError as e: raise SystemExit(f'{path}: {e.code} {e.read().decode()[:500]}')
    return json.loads(data) if data else None
statepath=root/'metabase/estado-local.json'
state=json.loads(statepath.read_text()) if statepath.exists() else {}
def save():statepath.write_text(json.dumps(state,indent=2))
definitions=json.loads((root/'metabase/definiciones.json').read_text(encoding='utf-8'))
if 'collection_id' not in state:
    state['collection_id']=api('POST','/collection',{'name':'Dashboard Académico UTM'})['id'];save()
def create_card(source,kind):
    card=dict(source);card['dataset_query']=dict(source['dataset_query']);card['dataset_query']['database']=args.database_id;card['collection_id']=state['collection_id'];card['type']=kind
    return api('POST','/card',card)['id']
if 'model_id' not in state:state['model_id']=create_card(definitions['model'],'model');save()
state.setdefault('cards',[])
for source in definitions['charts'][len(state['cards']):]:state['cards'].append(create_card(source,'question'));save()
if 'dashboard_id' not in state:
    state['dashboard_id']=api('POST','/dashboard',{'name':'Dashboard Académico UTM','collection_id':state['collection_id'],'description':'12,000 estudiantes únicos. Dispersión: muestra de 2,000. Aprobación: >=40%.','parameters':[{'id':'internet','name':'Acceso a internet (1=Sí, 0=No)','slug':'internet','type':'number/='}]})['id'];save()
did=state['dashboard_id']
if not state.get('layout_done'):
    cards=[{'id':-i-1,'card_id':id,'row':(i//2)*9,'col':(i%2)*12,'size_x':24 if i==4 else 12,'size_y':9,'series':[],'visualization_settings':{},'parameter_mappings':[{'parameter_id':'internet','card_id':id,'target':['variable',['template-tag','internet']]}] if i==3 else []} for i,id in enumerate(state['cards'])]
    api('PUT',f'/dashboard/{did}/cards',{'cards':cards});state['layout_done']=True;save()
settings={x['key']:x['value'] for x in api('GET','/setting')}
if not settings.get('enable-embedding-static'):api('PUT','/setting/enable-embedding-static',{'value':True})
secret=settings.get('embedding-secret-key')
if not secret:secret=secrets.token_hex(32);api('PUT','/setting/embedding-secret-key',{'value':secret})
api('PUT',f'/dashboard/{did}',{'enable_embedding':True,'embedding_params':{'internet':'locked'}})
envfile=root/'backend/.env'
if envfile.exists():
    env=envfile.read_text(encoding='utf-8').splitlines();updates={'METABASE_SITE_URL':args.url.rstrip('/'),'METABASE_DASHBOARD_ID':str(did),'METABASE_EMBEDDING_SECRET_KEY':secret}
    output=[]
    for line in env:
        name=line.split('=',1)[0]
        output.append(name+'='+updates.pop(name) if name in updates else line)
    output += [k+'='+v for k,v in updates.items()];envfile.write_text('\n'.join(output)+'\n',encoding='utf-8')
print('Configuración lista:',args.url.rstrip('/')+'/dashboard/'+str(did))
print('La clave API no fue guardada. La clave de firma permanece en backend/.env.')
