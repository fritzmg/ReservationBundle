function getWeekdate(e){var t,n,i,a;return 0<e.indexOf(".")?(n=(t=e.split("."))[2],i=t[1],a=t[0],e=(n=n&&2==n.length?"20"+n:n)+"/"+(i=i<10?"0"+i:i)+"/"+(a=a<10?"0"+a:a)):0<e.indexOf("/")&&(n=(t=e.split("/"))[2],i=t[1],a=t[0],e=(n=n&&2==n.length?"20"+n:n)+"/"+(i=i<10?"0"+i:i)+"/"+(a=a<10?"0"+a:a)),new Date(e).getDay()}function isWeekday(e,t){e=e.split("--");return!!(e&&e[0]&&e[1]&&getWeekdate(e[0])==e[1])}function setObjectId(e,t,n=0){var a=!!e&&e.getAttribute("data-object"),e=t,l=document.getElementById("c4g_reservation_object_"+e),s=document.getElementsByClassName("displayReservationObjects"),t="",r=!1;if(l){if(l.style.display="block",r=l.value&&0<parseInt(l.value)?l.value:r,s)for(i=0;i<s.length;i++)s[i].style.display="block";if(a){var o=a.split("-"),d=!1;for(i=0;i<o.length;i++){for(j=0;j<l.options.length;j++)if(!l.options[j].getAttribute("hidden")&&l.options[j].value==o[i]){l.value=o[i],handleBrickConditions(),d=!0;break}if(d)break}t=o||t}}if(hideOptions(e,t,n),r)for(i=0;i<l.options.length;i++)if(!l.options[i].getAttribute("hidden")&&l.options[i].value==r){l.value=r,handleBrickConditions();break}if(l&&-1!=parseInt(l.value))for(i=0;i<l.options.length;i++)if(-1==parseInt(l.options[i].value)&&"none"!=l.options[i].style.display){l.options[i].style.display="none";break}return hideOptions(e,t,n),!0}function hideOptions(e,t,n){var a;-1==e&&(e=(a=document.getElementById("c4g_reservation_type"))?a.value:-1);var l=document.getElementById("c4g_reservation_object_"+e),s=-1,r=0,o=!1;if(l){var d=0;for(i=0;i<l.options.length;i++){var c=l.options[i],m=c.getAttribute("min")?parseInt(c.getAttribute("min")):1,u=c.getAttribute("max")?parseInt(c.getAttribute("max")):0,g=document.getElementById("c4g_desiredCapacity_"+e),y=g?g.value:0,p=y-m,g=u-y,v=g<p?p:g;c.value&&-1==c.value&&(o=i);var b=!1;if(Array.isArray(t)){if(m&&y&&0<y&&m<=y&&y<=u)for(j=0;j<t.length;j++)t[j]==c.value&&(!d||0==d||v&&0<v&&v<d)&&(d=v||d,r=t[j],b=!0);else for(j=0;j<t.length;j++)if(t[j]==c.value){0==j&&(r=t[j]),b=!0;break}}else 0<=parseInt(t)&&t==c.value&&(r=t,b=!0);if(b||-1==c.value?-1!=c.value&&(c.removeAttribute("disabled"),c.removeAttribute("hidden"),m&&y&&0<y&&(y<m||u&&u<y)?(c.setAttribute("disabled","disabled"),c.setAttribute("hidden","hidden")):(c.removeAttribute("disabled"),c.removeAttribute("hidden"),0<=r&&c.value==r?s=r:-1==s&&-1!=c.value&&(s=c.value))):(c.setAttribute("disabled","disabled"),c.setAttribute("hidden","hidden")),n&&-1!=c.value&&b){var u=c.textContent,f="",_="",h=document.querySelectorAll(".c4g__form-date-container .c4g_beginDate_"+e);if(h)for(k=0;k<h.length;k++){var E=h[k];if(E&&E.value){f=E.value;break}}var I=document.querySelectorAll(".reservation_time_button_"+e+' input[type = "radio"]:checked');if(I)for(k=0;k<I.length;k++){var B=I[k];if(B){B=B.parentNode.getElementsByTagName("label")[0],_=B?B.value:"";break}}u&&""!=f&&""!=_&&(-1!=(y=u.lastIndexOf(" ("))&&(u=u.substr(0,y)),c.textContent=u+" ("+f+" "+_+")")}}!l.disabled&&l.value&&0<=l.value&&(s=l.value),0<=parseInt(s)?(l.options[s]?(l.value=s,l.options[s].removeAttribute("disabled"),l.options[s].removeAttribute("hidden")):0!=o&&(l.options[o].setAttribute("disabled","disabled"),l.options[o].setAttribute("hidden","hidden")),l.removeAttribute("disabled")):(0!=o&&(l.options[o].removeAttribute("disabled"),l.options[o].removeAttribute("hidden")),l.value=-1,l.setAttribute("disabled","disabled")),eventFire(l,"change")}checkEventFields()}function checkType(e,t){return t?!!e.parent.parent.classList.contains("begindate-event"):!!e.parent.parent.classList.contains("begin-date")}function setReservationForm(e,t){var n=!(document.getElementsByClassName("reservation-id")[0].style.display="none"),a=!1,l=document.getElementById("c4g_reservation_type");e=l?l.value:-1;var s=l.selectedIndex,s=l.options[s];if(s&&(n=2==s.getAttribute("type"),a=3==s.getAttribute("type")),0<e){s=document.getElementById("c4g_desiredCapacity_"+e);s&&(r=s.value,s.getAttribute("max")&&r>parseInt(s.getAttribute("max"))&&(s.value=s.getAttribute("max")),s.getAttribute("min")&&r<parseInt(s.getAttribute("min"))&&(s.value=s.getAttribute("min")));var r,s=document.getElementById("c4g_duration_"+e);s&&"none"!==s.style.display&&(s.style.display="block",r=s.value,s&&s.getAttribute("max")&&r>parseInt(s.getAttribute("max"))&&(s.value=s.getAttribute("max")),s&&s.getAttribute("min")&&r<parseInt(s.getAttribute("min"))&&(s.value=s.getAttribute("min")));s="c4g_beginDate_"+e;if(a){var o=document.getElementById("c4g_reservation_object_"+e);o&&(s=s+"-33"+o.value,setTimeset(document.getElementById(s),e,t))}else if(n){o=window.location.search;const u=new URLSearchParams(o);o=u.get("event");if(o){s="c4g_beginDateEvent_"+e+"-22"+o;document.getElementById(s)&&(setTimeset(document.getElementById(s),e,t),checkEventFields())}else{var d=document.getElementsByClassName("c4g__form-date-input");if(d)for(i=0;i<d.length;i++){var c=d[i];if(c&&checkType(c,n)&&c.value){var m=c.id;if(m&&m.indexOf("c4g_beginDateEvent_"+e+"-22")){setTimeset(c,e,t),checkEventFields();break}}}}}else document.getElementById(s)&&setTimeset(document.getElementById(s),e,t)}handleBrickConditions(),document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"}function checkTimelist(a,l){var s=-1;if(a&&l)for(idx=0;idx<l.length;idx++){let i=0;if(l[idx]){let e=[],t=l[idx].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let n=[];if((a=a.toString()).indexOf("#")?n=a.split("#"):n[0]=a,parseInt(e[0])===parseInt(n[0])&&(s=idx,i++),e[1]&&n[1]){var r=parseInt(e[0]),o=r+parseInt(e[1]),d=parseInt(n[0]),c=d+parseInt(n[1]);if(r<=d&&d<o&&(s=idx,i++),r<c&&c<=o&&(s=idx,i++),3==i)break}else if(e[1]&&n[0]){var m=parseInt(e[0]),u=m+parseInt(e[1]),g=parseInt(n[0]);if(m<=g&&g<=u&&(s=idx,i++),3==i)break}else if(e[0]&&n[1]){m=parseInt(e[0]),g=parseInt(n[0]),u=g+parseInt(n[1]);if(g<=m&&m<=u&&(s=idx,i++),3==i)break}}else if(1==i)break}return s}function checkMax(a,l,s,r,o,e){let d=!0;var c,m,u,g,e=a[l][s].act+parseInt(e);if(a[l][s].max&&e<=a[l][s].max){for(y=0;y<a.length;y++)if(r&&o&&y!=l){let e=[],t=o[y].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let n=[];(r=r.toString()).indexOf("#")?n=r.split("#"):n[0]=r;let i=!1;if(parseInt(e[0])===parseInt(n[0])?i=!0:e[1]&&n[1]&&(m=(c=parseInt(e[0]))+parseInt(e[1]),g=(u=parseInt(n[0]))+parseInt(n[1]),(c<=u&&u<m||c<g&&g<=m)&&(i=!0)),i)for(z=0;z<a[y].length;z++)if(a[y][z].max&&a[y][z].act>=a[y][z].max||a[y][z].act+a[l][s].act>=a[l][s].max)return!1;d=!0}}else d=!a[l][s].max;return d}function shuffle(e){let t=e.length;for(;0<t;){var n=Math.floor(Math.random()*t);t--;var i=e[t];e[t]=e[n],e[n]=i}return e}function addRadioFieldSet(n,e,a,l,s){var r,o=e.times;let d="";if(n)for(;n.firstChild;)n.firstChild.remove();for(r in o){var c=o[r].name,m=(o[r].interval,o[r].time),u=o[r].objects;let e=[],t="";var g=document.getElementById("c4g_reservation_object_"+a),y="",p=1,v=0;if(g)for(i=0;i<g.options.length;i++){var b=g.options[i],f=b.getAttribute("min")?parseInt(b.getAttribute("min")):1;(-1==f||f<p)&&(p=f);b=b.getAttribute("max")?parseInt(b.getAttribute("max")):0;(-1==b||v<b)&&(v=b)}if(!l||p<=l&&(!v||l<=v))for(u=shuffle(u),j=0;j<u.length;j++){var _=u[j];-1!=parseInt(_.id)&&(_.percent,_.priority&&1==_.priority?e.splice(0,0,_.id):e.push(_.id))}else y=!0;for(j=0;j<e.length;j++)0==j?d+=e[j]:d=d+"-"+e[j];t&&-1!=parseInt(t)||(t=e[0]);var h=document.createElement("div");h.className="c4g__form-check";var E=document.createElement("input");E.type="radio",E.className="c4g__form-check-input c4g__btn-check",E.id="beginTime_"+a+"-"+m,E.setAttribute("name","_c4g_beginTime_"+a),E.setAttribute("data-object",d),E.setAttribute("onchange","setObjectId(this,"+a+","+s+");"),E.setAttribute("onclick","document.getElementById('c4g_beginTime_"+a+"').value=this.value;"),E.setAttribute("value",m),E.style="display: block;",y&&(E.setAttribute("disabled",y),E.setAttribute("hidden",y)),E.className=E.className+" radio_object_hurry_up",h.appendChild(E);y=document.createElement("label");y.className="c4g__form-check-label c4g__btn c4g__btn-radio",y.innerText=c,y.htmlFor=E.id,h.appendChild(y),n.appendChild(h)}return d}function setTimeset(e,c,m){var t=0,u=0;-1==c?(document.getElementsByClassName("reservation_time_button")&&(document.getElementsByClassName("reservation_time_button").style.display="none"),document.getElementsByClassName("displayReservationObjects")&&(document.getElementsByClassName("displayReservationObjects").style.display="none")):(e?e.id&&e.id.indexOf("-33")&&-1!=e.id.indexOf("-33")&&(u=e.id.substr(e.id.indexOf("-33")+3)):e=document.getElementById("c4g_beginDate_"+c),t=e?e.value:0);e=document.getElementById("c4g_duration_"+c);e&&e.style&&"none"!==e.style&&(n=e.value);var n,g,y,e=document.getElementById("c4g_desiredCapacity_"+c);e&&e.style&&"none"!==e.style&&(g=e.value),(t=t&&t.indexOf("/")?(t=t.replace("/","~")).replace("/","~"):t)&&c&&(n=n||-1,g=g||-1,y=!(document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="flex"),fetch("/reservation-api/currentTimeset/"+t+"/"+c+"/"+n+"/"+g+"/"+u).then(e=>e.json()).then(e=>{var t=addRadioFieldSet(document.querySelector(".reservation_time_button_"+c+" fieldset"),e,c,g,m),n=document.getElementById("c4g_reservation_object_"+c),i=e.captions;document.getElementById("c4g_reservation_id").value&&document.getElementById("c4g_reservation_id").value==e.reservationId||(document.getElementById("c4g_reservation_id").value=e.reservationId),document.getElementsByClassName("reservation-id")[0].style.display="block";document.getElementsByClassName("reservation_time_button_"+c);if(u){if(i&&i[u]){var a=document.getElementById("c4g_reservation_object_"+c);if(a&&a.length)for(z=0;z<a.options.length;z++)if(a.options[z].value==u){a.options[z].innerHtml=i[u];break}}}else hideOptions(c,t,m);if(handleBrickConditions(),-1!=c){var l=document.querySelectorAll(".reservation_time_button_"+c+'.formdata input[type = "hidden"]'),s=!1;if(l)for(z=0;z<l.length;z++)if("none"!=l[z].style.display){s=l[z].value;break}var r=document.querySelectorAll(".reservation_time_button_"+c+' input[type = "radio"]'),o=[];if(r&&r.length)for(z=0;z<r.length;z++){var d=r[z];d&&(s&&d.value===s?y=d:d.value&&o.push(d))}if(!y&&o&&1===o.length)for(z=0;z<o.length;z++){y=o[z];break}if(!u&&n&&(setObjectId(0,c,m),n.value=-1,eventFire(n,"change"),n.disabled=!0),!u)if(!y||y.disabled||y.classList.contains("radio_object_disabled")){for(z=0;z<o.length;z++)o[z].removeAttribute("checked");n&&(n.value=-1,eventFire(n,"change"),n.disabled=!0)}else y.removeAttribute("checked"),eventFire(y,"click"),setObjectId(y,c,m)}}).finally(function(){document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"}))}function checkEventFields(){var e=document.getElementById("c4g_reservation_type"),t=e?e.value:-1,n=document.querySelector(".reservation-event-object select");let a=document.getElementsByClassName("eventdata");if(a[0]&&(a[0].style.display="none"),n&&document.querySelector("reservation-id:not([hidden])")){for(document.getElementsByClassName("reservation-id"),i=0;i<n.length;i++)if(n[i]){var l,s,r=-1;if((n=n[i])[i].value&&(r=t.toString()+"-22"+n[i].value.toString(),document.getElementsByClassName("eventdata_"+r).style.display="block",document.getElementsByClassName("eventdata_"+r).children[0].style.display="block"),l=document.getElementsByClassName("begindate-event"))for(j=0;j<l.length;j++)-1!=r&&l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].children[0].getElementsByTagName("input")[0].classList.contains("c4g_beginDateEvent_"+r)?(l[j].style.display="block",l[j].children[0].getElementsByTagName("label")[0].style.display="block",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].style.display="block",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].children[0].getElementsByTagName("input")[0].style.display="block"):(l[j].style.display="none",l[j].getElementsByTagName("label")[0].style.display="none",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].style.display="none",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].children[0].getElementsByTagName("input")[0].style.display="none");if(s=document.getElementsByClassName("reservation_time_event_button"))for(j=0;j<s.length;j++)-1!=r&&s[j].classList.contains("reservation_time_event_button_"+r)?(s[j].style.display="block",s[j].children[0].getElementsByTagName("label")[0].style.display="block",s[j].parent.style.display="block",s[j].parent.parent.style.display="block",s[j].parent.parent.parent.style.display="block"):(s[j].style.display="none",s[j].children[0].getElementsByTagName("label")[0].style.display="none",s[j].parent.style.display="none",s[j].parent.parent.style.display="none",s[j].parent.parent.parent.style.display="none")}}else{if((l=document.getElementsByClassName("begindate-event"))&&Array.isArray(l))for(i=0;i<l.length;i++)l[i].style.display="none";if((s=document.getElementsByClassName("reservation_time_event_button"))&&Array.isArray(s))for(i=0;i<s.length;i++)s[i].style.display="none"}}