package edu.tamucc.hri.griidc.utils;

import java.util.Iterator;
import java.util.Vector;

/**
 * a collection of messages or other strings
 * User can add messages incrementally and 
 * print the out to std out or std err
 * @author jvh
 *
 */
public class MessageContainer {

	private Vector<String> messages = new Vector<String>();
	
	public MessageContainer() {
	}
	
	public void initialize() {
		this.messages = new Vector<String>();
	}

	public String add(String msg) {
		this.getContainer().add(msg);
		return msg;
	}
	
	private Vector<String> getContainer() {
	  if(this.messages == null) {
		  this.messages = new Vector<String>();
	  }
	  return this.messages;
	}
	
	public Iterator<String> iterator() {
		return  this.getContainer().iterator();
	}
	public void toOut() {
		if(this.size() <= 0) return;
		Iterator<String> it = this.iterator();
		while (it.hasNext()) {
			String s = it.next();
			System.out.println(s);
		}
	}
	
	public void toErr() {
		if(this.size() <= 0) return;
		Iterator<String> it = this.iterator();
		while (it.hasNext()) {
			String s = it.next();
			System.err.println(s);
		}
	}
	
	public int size() {
		return this.getContainer().size();
	}
}
