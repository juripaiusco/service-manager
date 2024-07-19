# serviceM - v4
🇮🇹Italian

## ServiceM è un software per la gestione di servizi in scadenza.

Per esempio, ti sarà capitato di offrire i tuoi servizi a qualche cliente, questi servizi se sono creati ad hoc di
certo non sono servizi con scadenza, ma se il tuo servizio è una manutenzione, oppure un'assistenza, o la rivendita
di abbonamenti, hai avuto a che fare con l'automazione delle scadenze.

ServiceM ti aiuta a mettere in fila le scadenze, capire quali servizi devi togliere dal tuo listino, quali sono i
clienti virtuosi e quali puoi farne a meno. In tutto questo ServiceM ti aiuta a comunicare con i clienti, inviando
avvisi di scadenza e loro sarnno liberi di rinnovare o meno i tuoi servizi.

Questo è ServiceM, un software web open source per la gestione di servizi a scadenza.

## Cosa puoi fare con ServiceM?
1. Hai un dashboard con tutti i dati sotto controllo;
2. Gestire i tuoi servizi;
3. Gestire i tuoi clienti;
4. Personalizzare gli avvisi di scadenza;
5. Collegando ServiceM a Fatture in Cloud, puoi inviare fatture ai tuoi clienti una volta che rinnovano il servizio;
6. Collegando ServiceM a Brevo (ex Send in Blue), inviare SMS di avviso ai tuoi clienti.

## Nel dettaglio
### 1. Dashboard
Puoi visualizzare tutti i servizi in scadenza, fare ricerce per servizio o per clienti. Visualizzare l'andamento delle
tue scadenze, per ogni mese quali sono le tue entrate e uscite. Capire quale servizio genera più utile.

### 2. Servizi
La gestione dei servizi è molto semplice, ti basta inserire il nome, il prezzo di vendita e di acquisto.

La cosa interessante è che un servizio può essere acquistato in vari modi, con ServiceM ho cercato di prevedere queste
impostazioni, per esempio puoi fare un unico acquisto e rivenderlo a più clienti o più semplicemente lo acquisti e lo
rivendi. In alternativa può essere un servizio acquistato mensilmente, ma rivenduto annualmente. Insomma ho cercato
di prevedere varie opzioni.

### 3. Clienti
Ti basta inserire il cliente e le sue scadenze, poi ServiceM farà la magia.

In alternativa se hai una gestione clienti più complessa, ho cercato di prevedere varie tipologie di clienti, ad esempio
potresti avere un cliente che possiede più società, se non le raggruppi il tuo report non sarà chiaro, perché potresti
avere clienti che generano poco utile legati ad aziende che ne generano molto, raggruppando questi clienti, avrai
una visione molto più chiara.

### 4. Avvisi di scadenza
Puoi creare la mail o l'sms com più ti piace, ci sono dei campi chiavi che ti permetto di personalizzare l'avviso
per ogni singolo cliente.

## Perché questo progetto è utile (secondo me)?
Non ho trovato molti progetti di questo tipo, ho visto progetti personalizzati dal dentista, ogni anno vado dal
dentista un paio di volte e mi arriva un avviso via email, il software che usa il mio dentita è personalizzato
anche se possiende almeno 3 filiali, questo significa che si è appoggiato ad una piccola realtà per creare il
software che gli serviva.

Ho cercato altri software che potessero rispondere a questa necessità, ma non ho trovato nulla che si allineasse
alla mia visione.

Così ho creato ServiceM, semplice e pulito, in più essendo una WebApp la si può tranquillamente utilizzare anche
su smartphone.

## Come iniziare?
Essendo un software web, per iniziare ti basta un hosting Linux con database MySQL.

Queste sono i requisiti:
- PHP >= 8.1
- Apache 2
- MariaDB 10

Una volta copiato ServiceM ti basta caricarlo sul tuo hosting oppure in locale, modificare il file .env con i dati
del tuo database e lanciare  questo comando:

```Terminal
php artisan migrate
```

Verrà creato il database, e poi potrai accedere a ServiceM, creare il tuo utente e modificare i tuoi servizi.

## Dove posso trovare più aiuto, se ne ho bisogno?
Per adesso puoi scriveremi direttamente su github.
