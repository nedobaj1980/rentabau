import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";

const geistSans = Geist({ variable: "--font-geist-sans", subsets: ["latin"] });
const geistMono = Geist_Mono({ variable: "--font-geist-mono", subsets: ["latin"] });
export const metadata: Metadata = { title: "Rent A Bau | Baumaschinen mieten in der Ostschweiz", description: "Baumaschinen für Private und Gewerbe. Online reservieren, abholen oder auf die Baustelle liefern lassen.", other: { "codex-preview": "development" }, icons: { icon: "/favicon.svg", shortcut: "/favicon.svg" } };
export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) { return <html lang="de"><body className={`${geistSans.variable} ${geistMono.variable}`}>{children}</body></html>; }
