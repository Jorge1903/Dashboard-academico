import React,{useEffect,useState,useCallback} from 'react';
import {createRoot} from 'react-dom/client';
import {GraduationCap,LayoutDashboard,RefreshCw,Users,ChartNoAxesCombined,Wifi,CheckCircle2,AlertCircle} from 'lucide-react';
import './styles.css';
function App(){
 const [internet,setInternet]=useState(''),[embed,setEmbed]=useState(null),[error,setError]=useState(''),[busy,setBusy]=useState(true),[loaded,setLoaded]=useState(false),[stats,setStats]=useState(null);
 const refresh=useCallback(async(signal)=>{setBusy(true);setError('');setLoaded(false);try{const r=await fetch('/api/dashboard/embed'+(internet===''?'':'?internet='+internet),{signal,cache:'no-store',headers:{Accept:'application/json'}});const d=await r.json();if(!r.ok)throw Error(d.message||'No se pudo cargar el dashboard.');setEmbed(d)}catch(e){if(e.name!=='AbortError')setError(e.message)}finally{if(!signal?.aborted)setBusy(false)}},[internet]);
 useEffect(()=>{const c=new AbortController();refresh(c.signal);return()=>c.abort()},[refresh]);
 useEffect(()=>{if(!embed)return;const t=setTimeout(()=>refresh(),Math.max(1000,embed.expires_at*1000-Date.now()-30000));return()=>clearTimeout(t)},[embed,refresh]);
 useEffect(()=>{const c=new AbortController();fetch('/api/summary',{signal:c.signal}).then(r=>{if(!r.ok)throw Error();return r.json()}).then(setStats).catch(()=>{});return()=>c.abort()},[]);
 const cards=[['Estudiantes analizados',stats?.students?.toLocaleString('es-MX'),Users,'Registros únicos del dataset'],['Calificación promedio',stats?stats.average+'%':null,ChartNoAxesCombined,'Escala de 0 a 100'],['Tasa de aprobación',stats?stats.pass_rate+'%':null,CheckCircle2,'Calificación de al menos 40%'],['Acceso a internet',stats?stats.internet_rate+'%':null,Wifi,'Sobre respuestas conocidas']];
 return (
  <div className="shell">
   <aside className="sidebar">
    <a className="brand" href="#"><span><GraduationCap size={25}/></span><div>Dashboard<small>ACADÉMICO UTM</small></div></a>
    <nav aria-label="Navegación principal"><button className="active" type="button" aria-current="page"><LayoutDashboard size={18}/>Dashboard</button></nav>
   </aside>
   <main>
    <header className="topbar"><span>Vista general</span><span className="dataset-badge">UTM</span></header>
    <section className="content">
     <div className="intro"><div><h1>Dashboard Académico UTM</h1><p>Resumen del rendimiento estudiantil.</p></div></div>
     <section className="stats" aria-label="Resumen del dataset">
      {cards.map(([label,value,Icon,note])=><article className="stat" key={label}><div><span>{label}</span><Icon size={18}/></div><strong>{value??'—'}</strong><small>{note}</small></article>)}
     </section>
     <div className="section-heading"><h2>Gráficas</h2></div>
     <section className="dashboard-panel">
      <div className="toolbar"><div><label htmlFor="internet">Acceso a internet</label><select id="internet" value={internet} onChange={e=>setInternet(e.target.value)}><option value="">Todos los estudiantes</option><option value="1">Con acceso a internet</option><option value="0">Sin acceso a internet</option></select></div><button className="button secondary" onClick={()=>refresh()} disabled={busy}><RefreshCw size={16} className={busy?'spin':''}/>Actualizar</button></div>
      <p className="filter-help">Este filtro se aplica a la gráfica de aprobación. Las otras cuatro muestran la población completa.</p>
      {error?<div className="error" role="alert"><AlertCircle/><h3>No se pudo conectar con el dashboard</h3><p>{error}</p><p>Comprueba que Metabase esté activo en el puerto 3000 y que el dashboard tenga la incrustación habilitada.</p><button className="button" onClick={()=>refresh()}>Reintentar</button></div>:<>{(!loaded||busy)&&<div className="loading" role="status"><RefreshCw size={18} className="spin"/>Cargando gráficas…</div>}{embed&&<iframe key={embed.url} title="Dashboard Académico UTM: cinco gráficas" src={embed.url} referrerPolicy="no-referrer" onLoad={()=>setLoaded(true)} allowFullScreen/>}</>}
     </section>
     <footer>Dashboard Académico UTM</footer>
    </section>
   </main>
  </div>
 );
}
createRoot(document.getElementById('root')).render(<App/>);
