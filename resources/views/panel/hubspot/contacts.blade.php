
<h1>Contactos HubSpot</h1>
<table border="1" cellpadding="5" style="z-index: 50; background: white; border-radius: 8px; border-collapse: collapse; width: 100%; max-width: 800px; margin-top: 20px;    ">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        @foreach($contacts as $contact)
        <tr>
            <td>{{ $contact['id'] }}</td>
            <td>{{ $contact['properties']['firstname'] ?? '' }} {{ $contact['properties']['lastname'] ?? '' }}</td>
            <td>{{ $contact['properties']['email'] ?? '' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

