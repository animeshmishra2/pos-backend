<!DOCTYPE html>
<html>
    <head>
         
        <style>
  
        
        table.table-bordered{
            border:2px solid black;
            margin-top:20px;
          }
        table.table-bordered > thead > tr > th{
            border:2px solid black;
        }
        table.table-bordered > tbody > tr > td{
            border:2px solid black;
        }

        </style>
</head>
    <body style="max-width:100%;">
        
      
          
                  <div style="text-align:center;">
                      <h2 class="text-primary pb-1">GHAR GHAR BAZAAR</h2>
                      <h4 class="text-primary pb-1">C-37,SEC-F,LDA COLONY KANPUR ROAD LUCKNOW-226012</h2>
                      <h5 class="text-primary pb-1">GSTIN : 09AAICG0011C1ZB</h5>
                  </div>
         
                
                        <table style="width:100%;">
                            <thead>
                                <tr>
                                   <th>S.no</th>
                                   <th>Desc</th>
                                   <th>GSTIN</th>
                                   <th>Invoice Date</th>
                                   <th>Invoice No.</th>
                                   <th>Invoice Value</th>
                                   <th>Local/Central</th>
                                   <th>Invoice Type</th>
                                   <th>Hsn Code</th>
                                   <th>Quantity</th>
                                   <th>Amount</th>
                                   <th>Taxable Amount</th>
                                   <th colspan="2">Sgst</th>
                                   <th colspan="2">Cgst</th>
                                   <th colspan="2">Igst</th>
                                   <th>Cess</th>
                                   <th>Total Gst</th>
                                </tr>
                                
                            </thead>
                            <tbody class="table table-bordered " >
                                <tr>
                                    <td colspan="10"><strong>B2B</strong></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                   
                                </tr>
                                 <tr>
                                    <td colspan="10"><strong>B2C (Large) Invoice</strong></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                     <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                </tr>
                               
                                 <tr>
                                    <td colspan="10"><strong>B2C (Small) Invoice</strong></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                     <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                </tr>
                          
                                @php $sl = 1; @endphp
                                @foreach($data['b2c_small_invoice'] as $r)
                                <tr>
                                    <td>{{$sl}}</td>
                                    <td>{{$r['desc']}}</td>
                                   
                                   
                                      
                                </tr>
                                @php $sl++ @endphp
                                
                                @endforeach
                                <tr>
                                    <td colspan="10"><strong>Export Invoices</strong></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                     <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="10"><strong>Tax Liability on Advance</strong></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                     <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                </tr>
                                <tr>
                                    <td colspan="10"><strong>Set/off Tax on Advance of prior period</strong></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                     <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                </tr>
                                 <tr style="border:1px solid black;">
                                    <td colspan="10"><strong>Gross Total</strong></td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                     <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                    <td>0.00</td>
                                </tr>
                          
                            </tbody>
                        </table>
                     
        
    </body>
</html>