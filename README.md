# serviceM - v4
Italiano

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
- MariaDB 10 oppure MySQL

Una volta copiato ServiceM ti basta caricarlo sul tuo hosting oppure in locale, modificare il file .env con i dati
del tuo database e lanciare  questo comando:

```Terminal
php artisan migrate
```

Verrà creato il database, e poi potrai accedere a ServiceM, creare il tuo utente e modificare i tuoi servizi.

## Dove posso trovare più aiuto, se ne ho bisogno?
Per adesso puoi scriveremi direttamente su github.

## Licenza
Distribuito sotto la licenza MIT. Vedi **LICENSE** per maggiori informazioni.

## Autori
Juri Paiusco - Sviluppatore - [GitHub](https://github.com/juripaiusco)

- - - - - - - - - - - - - - - - - - - - - - - -

English

## ServiceM is software for managing expiring services.

For example, you will have happened to offer your services to some customers, these services if they are created ad hoc
certainly they are not services with an expiry date, but if your service is maintenance, or assistance, or resale
of subscriptions, you have had to deal with the automation of deadlines.

ServiceM helps you line up the deadlines, understand which services you need to remove from your price list, which ones are the
virtuous customers and which ones you can do without. In all this, ServiceM helps you communicate with customers by sending
expiration notices and they will be free to renew your services or not.

This is ServiceM, an open source web software for managing time-bound services.

## What can you do with ServiceM?
1. You have a dashboard with all the data under control;
2. Manage your services;
3. Manage your customers;
4. Customize expiration notices;
5. By connecting ServiceM to Cloud Invoices, you can send invoices to your customers once they renew the service;
6. By connecting ServiceM to Brevo (formerly Send in Blue), send SMS alerts to your customers.

## In detail
### 1. Dashboard
You can view all expiring services, search by service or by customer. View the progress of
your deadlines, for each month what your income and expenses are. Understand which service generates the most profit.

### 2. Services
Managing the services is very simple, you just need to enter the name, sales and purchase price.

The interesting thing is that a service can be purchased in various ways, with ServiceM I tried to predict these
settings, for example you can make a single purchase and resell it to multiple customers or more simply buy it and
resell. Alternatively it can be a service purchased monthly, but resold annually. In short, I searched
to foresee various options.

### 3. Customers
Just enter the customer and their deadlines, then ServiceM will do the magic.

Alternatively, if you have more complex customer management, I tried to predict various types of customers, for example
you may have a client who owns multiple companies, if you don't group them your report won't be clear, because you might
having customers who generate little profit linked to companies that generate a lot, by grouping these customers, you will have
a much clearer vision.

### 4. Expiration notices
You can create the email or SMS you like, there are key fields that allow you to customize the alert
for every single customer.

## Why is this project useful (in my opinion)?
I haven't found many projects of this type, I have seen customized projects at the dentist, every year I go to the
dentist a couple of times and I get an email alert, the software my dentist uses is customized
even if it has at least 3 branches, this means that it relied on a small company to create the
software he needed.

I looked for other software that could meet this need, but I didn't find anything that aligned
to my vision.

So I created ServiceM, simple and clean, plus being a WebApp you can easily use it too
on smartphones.

## How to get started?
Being a web software, all you need to get started is Linux hosting with a MySQL database.

These are the requirements:
- PHP >= 8.1
- Apache 2
- MariaDB 10 or MySQL

Once you have copied ServiceM, simply upload it to your hosting or locally, edit the .env file with the data
of your database and run this command:

```Terminal
php artisan migrate
```

The database will be created, and then you can log in to ServiceM, create your user and edit your services.

## Where can I find more help if I need it?
For now you can write to me directly on github.

## License
Distributed under the MIT license. See **LICENSE** for more information.

## Authors
Juri Paiusco - Developer - [GitHub](https://github.com/juripaiusco)
