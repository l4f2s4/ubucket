const repo=document.getElementById('mytable');

if(repo){
repo.addEventListener('click',e=>{
 if(e.target.className ==='btn btn-danger btn-md delete-repository'){
     if(confirm('Are you sure you want to delete ?')){
     const id=e.target.getAttribute('data-id');
      fetch('/dashboard/delete/${id}',{method: 'DELETE'}).then(res => window.location.reload());
     }
 }
});
}