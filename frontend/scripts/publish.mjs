import {cpSync,mkdirSync,readdirSync,existsSync,unlinkSync} from 'node:fs';
mkdirSync('../backend/public/app',{recursive:true});
const assets='../backend/public/app/assets';
if(existsSync(assets)) for(const file of readdirSync(assets,{withFileTypes:true})) {
  if(file.isFile()&&!existsSync('dist/assets/'+file.name)) unlinkSync(assets+'/'+file.name);
}
cpSync('dist','../backend/public/app',{recursive:true});
console.log('Frontend publicado en Laravel: http://127.0.0.1:8000');
