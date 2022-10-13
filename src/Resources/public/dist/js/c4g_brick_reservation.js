function setObjectId(e,t,n=0){var a=!!e&&e.getAttribute("data-object"),e=t,l=document.getElementById("c4g_reservation_object_"+e),r=document.getElementsByClassName("displayReservationObjects"),t="",s=!1;if(l){if(l.style.display="block",s=l.value&&0<parseInt(l.value)?l.value:s,r)for(i=0;i<r.length;i++)r[i].style.display="block";if(a){var o=a.split("-"),d=!1;for(i=0;i<o.length;i++){for(j=0;j<l.options.length;j++)if(!l.options[j].getAttribute("hidden")&&l.options[j].value==o[i]){l.value=o[i],handleBrickConditions(),d=!0;break}if(d)break}t=o||t}}if(hideOptions(e,t,n),s)for(i=0;i<l.options.length;i++)if(!l.options[i].getAttribute("hidden")&&!l.options[i].getAttribute("disabled")&&l.options[i].value==s){l.value=s,handleBrickConditions();break}if(l&&-1!==parseInt(l.value))for(i=0;i<l.options.length;i++)if(-1==parseInt(l.options[i].value)&&"none"!=l.options[i].style.display){l.options[i].style.display="none";break}return!0}function hideOptions(e,t,n){var a;-1==e&&(e=(a=document.getElementById("c4g_reservation_type"))?a.value:-1);var l=document.getElementById("c4g_reservation_object_"+e),r=!1,s=0,o=!1;if(l){var d=0;for(i=0;i<l.options.length;i++){var c=l.options[i],u=c.getAttribute("min")?parseInt(c.getAttribute("min")):1,m=c.getAttribute("max")?parseInt(c.getAttribute("max")):0,g=document.getElementById("c4g_desiredCapacity_"+e),v=g?g.value:0,p=v-u,g=m-v,y=g<p?p:g;c.value&&-1==parseInt(c.value)&&(o=i);var b=!1;if(Array.isArray(t)){if(u&&v&&0<v&&u<=v&&v<=m)for(j=0;j<t.length;j++)t[j]==c.value&&(!d||0==d||y&&0<y&&y<d)&&(d=y||d,s=t[j],b=!0);else for(j=0;j<t.length;j++)if(t[j]==c.value){0==j&&(s=t[j]),b=!0;break}}else 0<=parseInt(t)&&t==c.value&&(s=t,b=!0);if(b||-1==c.value?-1!=c.value&&(c.removeAttribute("disabled"),c.removeAttribute("hidden"),u&&v&&0<v?v<u||m&&m<v?(c.setAttribute("disabled","disabled"),c.setAttribute("hidden","hidden")):(c.removeAttribute("disabled"),c.removeAttribute("hidden"),(0<=s&&c.value==s||-1==r&&-1!=c.value)&&(r=i)):(c.removeAttribute("disabled"),c.removeAttribute("hidden"),(0<=s&&c.value==s||-1==l.value&&-1!=c.value)&&(r=i))):(c.setAttribute("disabled","disabled"),c.setAttribute("hidden","hidden")),n&&-1!=c.value&&b){var p=c.textContent,f="",_="",h=document.querySelectorAll(".c4g__form-date-container .c4g_beginDate_"+e);if(h)for(B=0;B<h.length;B++){var A=h[B];if(A&&A.value){f=A.value;break}}var I=document.querySelectorAll(".reservation_time_button_"+e+' input[type = "radio"]:checked');if(I&&I[0]){for(var E=document.getElementsByClassName("c4g__form-check-label"),B=0;B<E.length;B++)if(E[B].htmlFor==I[0].id){_=E[B].textContent;break}p&&""!=f&&""!=_&&(g=p.lastIndexOf(")"),u="",-1!=(m=p.lastIndexOf(" ("))&&-1!=g&&m<g&&(u=-1!==(v=p.indexOf(";"))?p.substr(m+2,v-m-2):p.substr(m+2,g-m-2)),-1!=m&&(p=p.substr(0,m)),u&&-1!==u.search(/€/)?c.textContent=p+" ("+u+"; "+f+" "+_+")":c.textContent=p+" ("+f+" "+_+")")}}}-1!==parseInt(r)&&l.options[r]?(l.value=l.options[r].value,l.options[r].removeAttribute("disabled"),l.options[r].removeAttribute("hidden"),0!=o&&(l.options[o].setAttribute("disabled","disabled"),l.options[o].setAttribute("hidden","hidden")),l.removeAttribute("disabled")):(0!=o&&(l.options[o].removeAttribute("disabled"),l.options[o].removeAttribute("hidden")),l.value=-1,l.setAttribute("disabled","disabled")),eventFire(l,"change")}checkEventFields()}function checkType(e,t){return t?!!e.parent.parent.classList.contains("begindate-event"):!!e.parent.parent.classList.contains("begin-date")}function setReservationForm(e,t){var n=!(document.getElementsByClassName("reservation-id")[0].style.display="none"),a=!1,l=document.getElementById("c4g_reservation_type");e=l?l.value:-1;var r=l.selectedIndex,r=l.options[r];if(r&&(n=2==r.getAttribute("type"),a=3==r.getAttribute("type")),0<e){r=document.getElementById("c4g_desiredCapacity_"+e);r&&(s=r.value,r.getAttribute("max")&&s>parseInt(r.getAttribute("max"))&&(r.value=r.getAttribute("max")),r.getAttribute("min")&&s<parseInt(r.getAttribute("min"))&&(r.value=r.getAttribute("min")));var s,r=document.getElementById("c4g_duration_"+e);r&&"none"!==r.style.display&&(r.style.display="block",s=r.value,r&&r.getAttribute("max")&&s>parseInt(r.getAttribute("max"))&&(r.value=r.getAttribute("max")),r&&r.getAttribute("min")&&s<parseInt(r.getAttribute("min"))&&(r.value=r.getAttribute("min")));r="c4g_beginDate_"+e;if(a){var o=document.getElementById("c4g_reservation_object_"+e);o&&(r=r+"-33"+o.value,setTimeset(document.getElementById(r).value,e,t,o.value))}else if(n){o=window.location.search;const m=new URLSearchParams(o);o=m.get("event");if(o){r="c4g_beginDateEvent_"+e+"-22"+o;document.getElementById(r)&&(setTimeset(document.getElementById(r).value,e,t,0),checkEventFields())}else{var d=document.getElementsByClassName("c4g__form-date-input");if(d)for(i=0;i<d.length;i++){var c=d[i];if(c&&checkType(c,n)&&c.value){var u=c.id;if(u&&u.indexOf("c4g_beginDateEvent_"+e+"-22")){setTimeset(c.value,e,t,0),checkEventFields();break}}}}}else document.getElementById(r)&&setTimeset(document.getElementById(r).value,e,t,0)}handleBrickConditions(),document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"}function checkTimelist(a,l){var r=-1;if(a&&l)for(idx=0;idx<l.length;idx++){let n=0;if(l[idx]){let e=[],t=l[idx].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let i=[];if((a=a.toString()).indexOf("#")?i=a.split("#"):i[0]=a,parseInt(e[0])===parseInt(i[0])&&(r=idx,n++),e[1]&&i[1]){var s=parseInt(e[0]),o=s+parseInt(e[1]),d=parseInt(i[0]),c=d+parseInt(i[1]);if(s<=d&&d<o&&(r=idx,n++),s<c&&c<=o&&(r=idx,n++),3==n)break}else if(e[1]&&i[0]){var u=parseInt(e[0]),m=u+parseInt(e[1]),g=parseInt(i[0]);if(u<=g&&g<=m&&(r=idx,n++),3==n)break}else if(e[0]&&i[1]){u=parseInt(e[0]),g=parseInt(i[0]),m=g+parseInt(i[1]);if(g<=u&&u<=m&&(r=idx,n++),3==n)break}}else if(1==n)break}return r}function checkMax(a,l,r,s,o,e){let d=!0;var c,u,m,g,e=a[l][r].act+parseInt(e);if(a[l][r].max&&e<=a[l][r].max){for(y=0;y<a.length;y++)if(s&&o&&y!=l){let e=[],t=o[y].toString();t&&t.indexOf("#")?e=t.split("#"):e[0]=t;let i=[];(s=s.toString()).indexOf("#")?i=s.split("#"):i[0]=s;let n=!1;if(parseInt(e[0])===parseInt(i[0])?n=!0:e[1]&&i[1]&&(u=(c=parseInt(e[0]))+parseInt(e[1]),g=(m=parseInt(i[0]))+parseInt(i[1]),(c<=m&&m<u||c<g&&g<=u)&&(n=!0)),n)for(z=0;z<a[y].length;z++)if(a[y][z].max&&a[y][z].act>=a[y][z].max||a[y][z].act+a[l][r].act>=a[l][r].max)return!1;d=!0}}else d=!a[l][r].max;return d}function shuffle(e){let t=e.length;for(;0<t;){var i=Math.floor(Math.random()*t);t--;var n=e[t];e[t]=e[i],e[i]=n}return e}function addRadioFieldSet(n,e,a,l,r,s){var o,d=e.times;if(n)for(;n.firstChild;)n.firstChild.remove();for(o in d){var c=d[o].name,u=d[o].interval,m=d[o].time,g=d[o].objects,v=0,p=document.getElementById("c4g_reservation_object_"+a),y="",b=1,f=0;if(p)for(i=0;i<p.options.length;i++){var _=p.options[i],h=_.getAttribute("min")?parseInt(_.getAttribute("min")):1;(-1==h||h<b)&&(b=h);_=_.getAttribute("max")?parseInt(_.getAttribute("max")):0;(-1==_||f<_)&&(f=_)}let e=[],t="";if(!l||-1==l||b<=l&&(!f||l<=f)){var g=shuffle(g),A=!0;for(j=0;j<g.length;j++){var I=g[j];-1!=parseInt(I.id)&&(v=I.percent,I.priority&&1==I.priority?e.splice(0,0,I.id):e.push(I.id),A=!1)}y=A}else y=!0;for(j=0;j<e.length;j++)0==j?t+=e[j]:t=t+"-"+e[j];var E=document.createElement("div");E.className="c4g__form-check";var B=document.createElement("input");B.type="radio",B.className="c4g__form-check-input c4g__btn-check",s?(B.setAttribute("name","_c4g_beginTime_"+a+"-33"+s),B.id="beginTime_"+a+"-33"+s+"-"+m+"#"+u,B.setAttribute("onclick","document.getElementById('c4g_beginTime_"+a+"-33"+s+"').value=this.value;")):(B.setAttribute("name","_c4g_beginTime_"+a),B.id="beginTime_"+a+"-"+m+"#"+u,B.setAttribute("onchange","setObjectId(this,"+a+","+r+");"),B.setAttribute("onclick","document.getElementById('c4g_beginTime_"+a+"').value=this.value;")),B.setAttribute("data-object",t),B.setAttribute("value",m+"#"+u),B.style="display: block;",y&&(B.setAttribute("disabled",y),B.setAttribute("hidden",y)),0<v&&(B.className=B.className+" radio_object_hurry_up"),E.appendChild(B);y=document.createElement("label");y.className="c4g__form-check-label c4g__btn c4g__btn-radio",y.innerText=c,y.htmlFor=B.id,E.appendChild(y),n.appendChild(E)}}function setTimeset(e,c,u,m){var t=-1,g=-1,n=document.getElementById("c4g_reservation_object_"+c);if(m)for(n.setAttribute("value",m),i=0;i<n.options.length;i++){let e=n.options[i];e.value==n.value?e.setAttribute("selected","true"):e.removeAttribute("selected")}else n&&(n.value=-1,eventFire(n,"change"));var a=document.getElementById("c4g_duration_"+c);a&&a.style&&"none"!==a.display&&(t=a.value);var v,a=document.getElementById("c4g_desiredCapacity_"+c);a&&a.style&&"none"!==a.display&&(g=a.value),(e=e&&e.indexOf("/")?(e=e.replace("/","~")).replace("/","~"):e)&&c&&(t=t||-1,g=g||-1,v=!(document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="flex"),fetch("/reservation-api/currentTimeset/"+e+"/"+c+"/"+t+"/"+g+"/"+m).then(e=>e.json()).then(e=>{var t=c;m&&(t+="-33"+m),addRadioFieldSet(document.querySelector(".radio-group-beginTime_"+t),e,c,g,u,m);var i=document.getElementById("c4g_reservation_object_"+c),n=e.captions;document.getElementById("c4g_reservation_id").value&&document.getElementById("c4g_reservation_id").value==e.reservationId||(document.getElementById("c4g_reservation_id").value=e.reservationId),document.getElementsByClassName("reservation-id")[0].style.display="block";document.getElementsByClassName("reservation_time_button_"+t);if(n&&n[m]){var a=document.getElementById("c4g_reservation_object_"+c);if(a&&a.length)for(z=0;z<a.options.length;z++)if(a.options[z].value==m){a.options[z].innerHtml=n[m];break}}if(handleBrickConditions(),-1!=c){var l=document.querySelectorAll(".reservation_time_button_"+t+'.formdata input[type = "hidden"]'),r=!1;if(l)for(z=0;z<l.length;z++)if("none"!=l[z].style.display){r=l[z].value;break}var s=document.querySelectorAll(".reservation_time_button_"+t+' input[type = "radio"]'),o=[];if(s&&s.length)for(z=0;z<s.length;z++){var d=s[z];!d||d.getAttribute("disabled")||d.getAttribute("hidden")||(r&&d.value===r?v=d:d.value&&o.push(d))}if(!v&&o&&1<=o.length)for(z=0;z<o.length;z++){v=o[z];break}if(!m&&i&&(setObjectId(0,c,u),i.value=-1,eventFire(i,"change"),i.disabled=!0),!m)if(!v||v.disabled||v.classList.contains("radio_object_disabled")){for(z=0;z<o.length;z++)o[z].removeAttribute("checked");i&&(i.value=-1,eventFire(i,"change"),i.disabled=!0)}else v.setAttribute("checked","checked"),document.getElementById("c4g_beginTime_"+c).value=v.value,setObjectId(v,c,u)}}).finally(function(){document.getElementsByClassName("c4g__spinner-wrapper")[0].style.display="none"}))}function checkEventFields(){var e=document.getElementById("c4g_reservation_type"),t=e?e.value:-1,n=document.querySelector(".reservation-event-object select");let a=document.getElementsByClassName("eventdata");if(a[0]&&(a[0].style.display="none"),n&&document.querySelector("reservation-id:not([hidden])")){for(document.getElementsByClassName("reservation-id"),i=0;i<n.length;i++)if(n[i]){var l,r,s=-1;if((n=n[i])[i].value&&(s=t.toString()+"-22"+n[i].value.toString(),document.getElementsByClassName("eventdata_"+s).style.display="block",document.getElementsByClassName("eventdata_"+s).children[0].style.display="block"),l=document.getElementsByClassName("begindate-event"))for(j=0;j<l.length;j++)-1!=s&&l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].children[0].getElementsByTagName("input")[0].classList.contains("c4g_beginDateEvent_"+s)?(l[j].style.display="block",l[j].children[0].getElementsByTagName("label")[0].style.display="block",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].style.display="block",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].children[0].getElementsByTagName("input")[0].style.display="block"):(l[j].style.display="none",l[j].getElementsByTagName("label")[0].style.display="none",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].style.display="none",l[j].children[0].getElementsByClassName("c4g__form-date-container")[0].children[0].getElementsByTagName("input")[0].style.display="none");if(r=document.getElementsByClassName("reservation_time_event_button"))for(j=0;j<r.length;j++)-1!=s&&r[j].classList.contains("reservation_time_event_button_"+s)?(r[j].style.display="block",r[j].children[0].getElementsByTagName("label")[0].style.display="block",r[j].parent.style.display="block",r[j].parent.parent.style.display="block",r[j].parent.parent.parent.style.display="block"):(r[j].style.display="none",r[j].children[0].getElementsByTagName("label")[0].style.display="none",r[j].parent.style.display="none",r[j].parent.parent.style.display="none",r[j].parent.parent.parent.style.display="none")}}else{if((l=document.getElementsByClassName("begindate-event"))&&Array.isArray(l))for(i=0;i<l.length;i++)l[i].style.display="none";if((r=document.getElementsByClassName("reservation_time_event_button"))&&Array.isArray(r))for(i=0;i<r.length;i++)r[i].style.display="none"}}