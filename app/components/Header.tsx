import Link from "next/link";
import { getChatGPTUser, chatGPTSignInPath } from "../chatgpt-auth";

export default async function Header() {
  const user = await getChatGPTUser();
  return <header className="site-header"><div className="shell nav-wrap">
    <Link href="/" className="brand" aria-label="Rent A Bau Startseite"><img src="/brand/rent-a-bau-logo.png" alt="Rent A Bau Maschinenvermietung" /></Link>
    <nav aria-label="Hauptnavigation"><Link href="/#maschinen">Maschinen</Link><Link href="/#ablauf">So funktioniert’s</Link><Link href="/#lieferung">Lieferung</Link></nav>
    <div className="nav-actions"><Link className="text-link" href={user ? "/konto" : chatGPTSignInPath("/konto")}>{user ? "Mein Konto" : "Anmelden"}</Link><Link className="button button-small" href="/#maschinen">Jetzt mieten</Link></div>
  </div></header>;
}
