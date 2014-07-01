package edu.tamucc.hri.griidc.xml;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.util.ArrayList;
import java.util.List;

import javax.xml.stream.XMLEventReader;
import javax.xml.stream.XMLInputFactory;
import javax.xml.stream.XMLStreamConstants;
import javax.xml.stream.XMLStreamException;
import javax.xml.stream.XMLStreamReader;

import edu.tamucc.hri.griidc.pubs.Publication;

public class PubsStaxXmlHandler {
	private XMLInputFactory xmlInputFactory = XMLInputFactory.newInstance();
    private XMLEventReader eventReader = null;
    private XMLStreamReader streamReader = null;
    
    private boolean bAuthors = false;
    private boolean bTitle = false;
    private boolean bPublisher = false;
    private boolean bPubAbstract = false;
    private boolean bDoi = false;
    private boolean bKey = false;
	public PubsStaxXmlHandler() {
		// TODO Auto-generated constructor stub
	}
	
	public void injestXmlFile(final String xmlFileName) throws FileNotFoundException, XMLStreamException {
		
		this.eventReader =
				xmlInputFactory.createXMLEventReader(
		        new FileReader(xmlFileName));

		this.streamReader =
				xmlInputFactory.createXMLStreamReader(
		        new FileReader(xmlFileName));
	}

	private  List<Publication> parseXmlCreatePublications(String fileName) {
        List<Publication> pubList = new ArrayList<Publication>();
        Publication pub = null;
        StringBuffer sb = null;
        XMLInputFactory xmlInputFactory = XMLInputFactory.newInstance();
        try {
            this.streamReader = this.xmlInputFactory.createXMLStreamReader(new FileInputStream(fileName));
            int event = this.streamReader.getEventType();
            while(true) {
                switch(event) {
           
                case XMLStreamConstants.START_ELEMENT:
                	/************************************
                    if(this.streamReader.getLocalName().equals("Publication")){
                        pub = new Publication();
                        pub.setId(Integer.parseInt(this.streamReader.getAttributeValue(0)));
                    }else if(this.streamReader.getLocalName().equals("name")){
                        bName=true;
                    }else if(this.streamReader.getLocalName().equals("age")){
                        bAge=true;
                    }else if(this.streamReader.getLocalName().equals("role")){
                        bRole=true;
                    }else if(this.streamReader.getLocalName().equals("gender")){
                        bGender=true;
                    }
                    *********************/
                    break;
                case XMLStreamConstants.CHARACTERS:
                	/*****************************
                    if(bName){
                        pub.setName(this.streamReader.getText());
                        bName=false;
                    }else if(bAge){
                        pub.setAge(Integer.parseInt(this.streamReader.getText()));
                        bAge=false;
                    }else if(bGender){
                        pub.setGender(this.streamReader.getText());
                        bGender=false;
                    }else if(bRole){
                        pub.setRole(this.streamReader.getText());
                        bRole=false;
                    }
                    ***********************/
                    break;
                case XMLStreamConstants.END_ELEMENT:
                	/*********************************
                    if(this.streamReader.getLocalName().equals("Publication")){
                        pubList.add(pub);
                    }
                    *******************/
                    break;
                }
                
                if (!this.streamReader.hasNext())
                    break;
 
              event = this.streamReader.next();
            }
             
        } catch (FileNotFoundException e)  {
            e.printStackTrace();
        } catch ( XMLStreamException e) {
        	 e.printStackTrace();
        }
        
        return pubList;
    }
	
	public  void parseXmlInterrogate(String fileName) {
        String startToken = null;
        String value = null;
        String endToken = null;
        XMLInputFactory xmlInputFactory = XMLInputFactory.newInstance();
        try {
            this.streamReader = this.xmlInputFactory.createXMLStreamReader(new FileInputStream(fileName));
            int event = this.streamReader.getEventType();
            while(true){
                switch(event) {
                case XMLStreamConstants.START_ELEMENT:
                	startToken = this.streamReader.getLocalName();
                    break;
                case XMLStreamConstants.CHARACTERS:
                    value = this.streamReader.getText();
                    break;
                case XMLStreamConstants.END_ELEMENT:
                	endToken = this.streamReader.getLocalName();
                	System.out.println("<" + startToken.trim() + ">: " + value.trim() + " <" + endToken.trim() + ">");
                    break;
                }
                if (!this.streamReader.hasNext())
                    break;
 
              event = this.streamReader.next();
            }
             
        } catch (FileNotFoundException e) {
            e.printStackTrace();
        } catch (XMLStreamException e) {
            e.printStackTrace();
        }
        return;
    }
}
