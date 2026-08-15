import ReactDOM from 'react-dom/client'
import Login from './login.tsx'
import { WebsiteTitle } from '../global.ts'
import { isAppBrowser } from "../Module/navigator.ts"


ReactDOM.createRoot(document.getElementById('root')!).render(
    <>
    <title>{WebsiteTitle}</title>
    <Login />
    </>
)

window.onload = function () {
  import('../Module/FastClick.ts');
}


if (isAppBrowser()) {
  window.location.href = "redirect.html";
}

