<?xml version="1.0" encoding="UTF-8"?>
<?adf version="1.0"?>
<adf>
 <prospect>
  <requestdate>{{ $requestDate }}</requestdate>
  <vehicle>
   <year>{{ $vehicleYear }}</year>
   <make>{{ $vehicleManufacturer }}</make>
   <model>{{ $vehicleModel }}</model>
   <stock>{{ $vehicleStock }}</stock>
   <vin>{{ $vehicleVin }}</vin>
  </vehicle>
  <customer>
   <contact>
    <name part="first">{{ $leadFirst }}</name>
    <name part="last">{{ $leadLast }}</name>
    <email>{{ $leadEmail }}</email>
    <phone>{{ $leadPhone }}</phone>
   </contact>
   <comments><![CDATA[{{ $leadComments }}]]></comments>
   <address type="home">
    <street>{{ $leadAddress }}</street>
    <city>{{ $leadCity }}</city>
    <regioncode>{{ $leadState }}</regioncode>
    <postalcode>{{ $leadPostal }}</postalcode>
   </address>
  </customer>
  <vendor>
   <id sequence="1" source="DealerID">{{ $dealerId }}</id>
   @if (!empty($dealerLocationId))
      <id sequence="2" source="DealerLocationID">{{ $dealerLocationId }}</id>
   @endif
   @if (!empty($leadId))
      <id sequence="3" source="ID">{{ $leadId }}</id>
   @endif
   <vendorname>{{ $vendorName }}</vendorname>
   <contact>
    <name part="full">{{ $vendorContact }}</name>
    <url>{{ $vendorWebsite }}</url>
    <email>{{ $vendorEmail }}</email>
    <phone>{{ $vendorPhone }}</phone>
    <address type="work">
     <street>{{ $vendorAddress }}</street>
     <city>{{ $vendorCity }}</city>
     <regioncode>{{ $vendorCity }}</regioncode>
     <postalcode>{{ $vendorPostal }}</postalcode>
     <country>{{ $vendorCountry }}</country>
    </address>
   </contact>
  </vendor>
  <provider>
   <name part="full">{{ $providerName }}</name>
  </provider>
 </prospect>
</adf>
